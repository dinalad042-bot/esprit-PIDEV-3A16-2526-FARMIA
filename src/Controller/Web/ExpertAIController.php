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
    public function __construct(
        private GroqService $groqService,
        private WeatherService $weatherService,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/analyse/{id}/diagnose', name: 'expert_analyse_diagnose', methods: ['POST'])]
    public function diagnose(Analyse $analyse): Response
    {
        // Security check: ensure the expert is the technicien for this analysis
        if ($analyse->getTechnicien() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à diagnostiquer cette analyse.');
        }

        // Check if analysis has an image
        if (!$analyse->getImageUrl()) {
            $this->addFlash('error', 'Aucune image disponible pour le diagnostic IA.');
            return $this->redirectToRoute('expert_analyse_show', ['id' => $analyse->getId()]);
        }

        try {
            // Run AI vision diagnostic
            $diagnosisResult = $this->groqService->generateVisionDiagnostic($analyse->getImageUrl());

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
                $weather = $this->weatherService->getWeatherForLocation($analyse->getFerme()->getLieu());
                $analyse->setWeatherData($weather);
                $analyse->setWeatherFetchedAt(new \DateTime());
            }

            $this->em->flush();

            $this->addFlash('success', 'Diagnostic IA effectué avec succès. Confiance: ' . $diagnosisResult->confidence);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du diagnostic IA: ' . $e->getMessage());
        }

        return $this->redirectToRoute('expert_analyse_show', ['id' => $analyse->getId()]);
    }

    #[Route('/analyse/{id}/ai-result', name: 'expert_analyse_ai_result', methods: ['GET'])]
    public function showAiResult(Analyse $analyse): Response
    {
        // Security check: ensure the expert is the technicien for this analysis
        if ($analyse->getTechnicien() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir ce diagnostic.');
        }

        if (!$analyse->hasAiDiagnosis()) {
            $this->addFlash('error', 'Aucun diagnostic IA disponible pour cette analyse.');
            return $this->redirectToRoute('expert_analyse_show', ['id' => $analyse->getId()]);
        }

        $aiResult = json_decode($analyse->getAiDiagnosisResult(), true);

        return $this->render('portal/expert/ai_result.html.twig', [
            'analyse' => $analyse,
            'aiResult' => $aiResult,
        ]);
    }

    #[Route('/analyse/{id}/diagnose/json', name: 'expert_analyse_diagnose_api', methods: ['POST'])]
    public function diagnoseApi(Analyse $analyse): JsonResponse
    {
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
                $weather = $this->weatherService->getWeatherForLocation($analyse->getFerme()->getLieu());
                $analyse->setWeatherData($weather);
                $analyse->setWeatherFetchedAt(new \DateTime());
            }
            
            $diagnosisResult = $this->groqService->generateVisionDiagnostic($analyse->getImageUrl());

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

            return new JsonResponse([
                'success' => true,
                'confidence' => $diagnosisResult->confidence,
                'condition' => $diagnosisResult->condition,
                'message' => 'Diagnostic completed successfully',
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
