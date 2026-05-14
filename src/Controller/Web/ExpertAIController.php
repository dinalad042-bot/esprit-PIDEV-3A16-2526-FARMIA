<?php

namespace App\Controller\Web;

use App\Entity\Analyse;
use App\Service\GroqService;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/expert')]
#[IsGranted('ROLE_EXPERT')]
class ExpertAIController extends AbstractController
{
    private string $debugLogFile;

    public function __construct(
        private GroqService $groqService,
        private WeatherService $weatherService,
        private EntityManagerInterface $em,
    ) {
        $this->debugLogFile = dirname(__DIR__, 3) . '/var/log/debug.log';
    }

    private function debug(string $msg): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[{$timestamp}] {$msg}\n";
        @file_put_contents($this->debugLogFile, $entry, FILE_APPEND);
    }

    #[Route('/analyse/{id}/diagnose', name: 'expert_analyse_diagnose', methods: ['POST'])]
    public function diagnose(Analyse $analyse): Response
    {
        $this->debug('=== DIAGNOSE CALLED ===');

        // Security check: ensure the expert is the technicien for this analysis
        if ($analyse->getTechnicien() !== $this->getUser()) {
            $this->debug('ACCESS DENIED - user is not technician');
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à diagnostiquer cette analyse.');
        }

        // Check if analysis has an image
        if (!$analyse->getImageUrl()) {
            $this->debug('NO IMAGE - redirecting');
            $this->addFlash('error', 'Aucune image disponible pour le diagnostic IA.');
            return $this->redirectToRoute('expert_analyse_show', ['id' => $analyse->getId()]);
        }

        $imageUrl = $analyse->getImageUrl();
        $this->debug("Image URL: $imageUrl");

        try {
            $this->debug('Calling groqService->generateVisionDiagnostic()...');

            // Debug: test resolveImageUrl first
            $reflection = new \ReflectionClass($this->groqService);
            $resolveMethod = $reflection->getMethod('resolveImageUrl');
            $resolveMethod->setAccessible(true);
            $resolvedUrl = $resolveMethod->invoke($this->groqService, $imageUrl);
            $this->debug('Resolved URL length: ' . strlen($resolvedUrl) . ' | starts with data: ' . (str_starts_with($resolvedUrl, 'data:') ? 'YES' : 'NO'));
            $this->debug('Resolved URL preview: ' . substr($resolvedUrl, 0, 100));

            $diagnosisResult = $this->groqService->generateVisionDiagnostic($imageUrl);
            $this->debug('Result returned, condition: ' . ($diagnosisResult->condition ?? 'null'));

            // Store results in analyse entity
            $analyse->setAiDiagnosisResult(json_encode([
                'condition' => $diagnosisResult->condition,
                'symptoms' => $diagnosisResult->symptoms,
                'treatment' => $diagnosisResult->treatment,
                'prevention' => $diagnosisResult->prevention,
                'urgency' => $diagnosisResult->urgency,
                'needsExpert' => $diagnosisResult->needsExpert,
                'rawResponse' => $diagnosisResult->rawResponse,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $analyse->setAiConfidenceScore($diagnosisResult->confidence);
            $analyse->setAiDiagnosisDate(new \DateTime());

            // Fetch weather data for farm location if available
            if ($analyse->getFerme()?->getLieu()) {
                $weather = $this->weatherService->getWeather($analyse->getFerme()->getLieu());
                $analyse->setWeatherData($weather);
                $analyse->setWeatherFetchedAt(new \DateTime());
            }

            $this->em->flush();

            $this->debug('SUCCESS - flushed to database');
            $this->addFlash('success', 'Diagnostic IA effectué avec succès. Confiance: ' . $diagnosisResult->confidence);

        } catch (\Exception $e) {
            $this->debug('EXCEPTION: ' . $e->getMessage());
            $this->debug('Trace: ' . $e->getTraceAsString());
            $this->addFlash('error', 'Erreur lors du diagnostic IA: ' . $e->getMessage());
        }

        return $this->redirectToRoute('expert_analyse_show', ['id' => $analyse->getId()]);
    }

    #[Route('/analyse/{id}/ai-result', name: 'expert_analyse_ai_result', methods: ['GET'])]
    public function showAiResult(Analyse $analyse): Response
    {
        $this->debug('=== AI RESULT PAGE CALLED ===');

        // Security check: ensure the expert is the technicien for this analysis
        if ($analyse->getTechnicien() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir ce diagnostic.');
        }

        if (!$analyse->hasAiDiagnosis()) {
            $this->debug('NO AI DIAGNOSIS - redirecting');
            $this->addFlash('error', 'Aucun diagnostic IA disponible pour cette analyse.');
            return $this->redirectToRoute('expert_analyse_show', ['id' => $analyse->getId()]);
        }

        // Parse AI result - handle both JSON and plain text formats
        $rawResult = $analyse->getAiDiagnosisResult();
        $aiResult = null;

        if ($rawResult && str_starts_with(trim($rawResult), '{')) {
            $aiResult = json_decode($rawResult, true);
        }

        // If JSON parsing failed or not JSON, create a structured fallback
        if ($aiResult === null) {
            $aiResult = [
                'condition' => 'Diagnostic IA',
                'confidence' => $analyse->getAiConfidenceScore() ?? 'Non évaluée',
                'urgency' => 'Surveiller',
                'needsExpert' => false,
                'symptoms' => $rawResult ?? 'Résultat non disponible',
                'treatment' => '',
                'prevention' => '',
                'rawResponse' => $rawResult ?? '',
            ];
        }

        $this->debug('Rendering ai_result.html.twig with aiResult');

        return $this->render('portal/expert/ai_result.html.twig', [
            'analyse' => $analyse,
            'aiResult' => $aiResult,
            'imageDataUri' => $this->convertImageUrlToDataUri($analyse->getImageUrl()),
        ]);
    }

    /**
     * Convert local image URL to base64 data URI for display.
     */
    private function convertImageUrlToDataUri(?string $imageUrl): ?string
    {
        if (empty($imageUrl)) {
            return null;
        }

        // Already a data URI or HTTP URL
        if (str_starts_with($imageUrl, 'data:') || str_starts_with($imageUrl, 'http')) {
            return $imageUrl;
        }

        // Windows path
        if (strlen($imageUrl) >= 3 && ctype_alpha($imageUrl[0]) && $imageUrl[1] === ':' && ($imageUrl[2] === '\\' || $imageUrl[2] === '/')) {
            $normalizedPath = str_replace('/', '\\', $imageUrl);
            if (file_exists($normalizedPath) && is_file($normalizedPath)) {
                $mimeType = mime_content_type($normalizedPath) ?: 'image/jpeg';
                $content = file_get_contents($normalizedPath);
                if ($content !== false) {
                    return 'data:' . $mimeType . ';base64,' . base64_encode($content);
                }
            }
        }

        // Relative path - try uploads
        if (str_starts_with($imageUrl, '/')) {
            $fullPath = dirname(__DIR__, 2) . '/public' . $imageUrl;
            if (file_exists($fullPath) && is_file($fullPath)) {
                $mimeType = mime_content_type($fullPath) ?: 'image/jpeg';
                $content = file_get_contents($fullPath);
                if ($content !== false) {
                    return 'data:' . $mimeType . ';base64,' . base64_encode($content);
                }
            }
        }

        return null;
    }

    #[Route('/analyse/{id}/diagnose/text', name: 'expert_analyse_diagnose_text', methods: ['GET'])]
    public function diagnoseText(Analyse $analyse): Response
    {
        $this->debug('=== DIAGNOSE TEXT CALLED ===');

        // Security check: ensure the expert is the technicien for this analysis
        if ($analyse->getTechnicien() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à diagnostiquer cette analyse.');
        }

        // Get observation from resultat_technique or description
        $observation = $analyse->getResultatTechnique() ?: $analyse->getDescriptionDemande() ?: '';

        if (empty($observation)) {
            $this->addFlash('error', 'Aucune observation disponible pour le diagnostic textuel.');
            return $this->redirectToRoute('expert_analyse_show', ['id' => $analyse->getId()]);
        }

        try {
            $this->debug('Calling groqService->generateTextDiagnostic()...');

            // Build context data
            $contextData = [];
            if ($analyse->getFerme()) {
                $contextData['ferme'] = [
                    'nom' => $analyse->getFerme()->getNomFerme(),
                    'lieu' => $analyse->getFerme()->getLieu(),
                ];
            }
            if ($analyse->getPlanteCible()) {
                $contextData['analyseCible'] = [
                    'nom' => $analyse->getPlanteCible()->getNom(),
                    'type' => 'plante',
                ];
            } elseif ($analyse->getAnimalCible()) {
                $contextData['analyseCible'] = [
                    'nom' => $analyse->getAnimalCible()->getEspece(),
                    'type' => 'animal',
                ];
            }

            $diagnosisResult = $this->groqService->generateTextDiagnostic($observation, $contextData);
            $this->debug('Text diagnostic result, condition: ' . ($diagnosisResult->condition ?? 'null'));

            // Store results
            $analyse->setAiDiagnosisResult(json_encode([
                'condition' => $diagnosisResult->condition,
                'symptoms' => $diagnosisResult->symptoms,
                'treatment' => $diagnosisResult->treatment,
                'prevention' => $diagnosisResult->prevention,
                'urgency' => $diagnosisResult->urgency,
                'needsExpert' => $diagnosisResult->needsExpert,
                'rawResponse' => $diagnosisResult->rawResponse,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $analyse->setAiConfidenceScore($diagnosisResult->confidence);
            $analyse->setAiDiagnosisDate(new \DateTime());

            $this->em->flush();

            $this->debug('SUCCESS - text diagnostic saved');
            $this->addFlash('success', 'Diagnostic IA textuel effectué. Confiance: ' . $diagnosisResult->confidence);

        } catch (\Exception $e) {
            $this->debug('TEXT DIAGNOSE EXCEPTION: ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors du diagnostic IA: ' . $e->getMessage());
        }

        return $this->redirectToRoute('expert_analyse_show', ['id' => $analyse->getId()]);
    }

    #[Route('/analyse/{id}/diagnose/json', name: 'expert_analyse_diagnose_api', methods: ['POST'])]
    public function diagnoseApi(Analyse $analyse): JsonResponse
    {
        $this->debug('=== DIAGNOSE API CALLED ===');

        // Security check
        if ($analyse->getTechnicien() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        if (!$analyse->getImageUrl()) {
            return new JsonResponse(['error' => 'No image available'], 400);
        }

        try {
            // Fetch weather FIRST before vision analysis
            if ($analyse->getFerme()?->getLieu()) {
                $weather = $this->weatherService->getWeather($analyse->getFerme()->getLieu());
                $analyse->setWeatherData($weather);
                $analyse->setWeatherFetchedAt(new \DateTime());
            }

            $diagnosisResult = $this->groqService->generateVisionDiagnostic($analyse->getImageUrl());

            // Store results
            $analyse->setAiDiagnosisResult(json_encode($diagnosisResult, JSON_PRETTY_PRINT));
            $analyse->setAiConfidenceScore($diagnosisResult->confidence);
            $analyse->setAiDiagnosisDate(new \DateTime());

            $this->em->flush();

            return new JsonResponse([
                'success' => true,
                'condition' => $diagnosisResult->condition,
                'confidence' => $diagnosisResult->confidence,
            ]);

        } catch (\Exception $e) {
            $this->debug('API EXCEPTION: ' . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}