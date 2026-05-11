<?php

namespace App\Service;

use App\DTO\DiagnosisResult;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GroqService
{
    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const VISION_MODEL = 'meta-llama/llama-4-scout-17b-16e-instruct';

    // Fallback model for vision (non-vision, text-only)
    private const VISION_FALLBACK_MODEL = 'llama-3.3-70b-versatile';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
        private string $model = 'meta-llama/llama-4-scout-17b-16e-instruct'
    ) {}

    // ─── Text Diagnostic ──────────────────────────────────────────────

    public function generateTextDiagnostic(string $observation, array $contextData = []): DiagnosisResult
    {
        // Build context section if data available
        $contextSection = '';
        if (!empty($contextData['ferme'])) {
            $contextSection .= "\nCONTEXTE DE LA FERME:\n";
            $contextSection .= "- Ferme: {$contextData['ferme']['nom']}\n";
            if ($contextData['ferme']['lieu']) {
                $contextSection .= "- Lieu: {$contextData['ferme']['lieu']}\n";
            }
            
            // Add related plantes
            if (!empty($contextData['plantes'])) {
                $contextSection .= "\nAutres plantes dans cette ferme:\n";
                foreach (array_slice($contextData['plantes'], 0, 5) as $plante) {
                    $contextSection .= "- {$plante['nom']}";
                    if ($plante['type']) {
                        $contextSection .= " (sol: {$plante['type']})";
                    }
                    $contextSection .= "\n";
                }
            }
            
            // Add related animaux
            if (!empty($contextData['animaux'])) {
                $contextSection .= "\nAutres animaux dans cette ferme:\n";
                foreach (array_slice($contextData['animaux'], 0, 5) as $animal) {
                    $contextSection .= "- {$animal['espece']}";
                    if ($animal['race']) {
                        $contextSection .= " (race: {$animal['race']})";
                    }
                    if ($animal['etat']) {
                        $contextSection .= " [état: {$animal['etat']}]";
                    }
                    $contextSection .= "\n";
                }
            }
            
            // Add what's being analyzed
            if (!empty($contextData['analyseCible']['type'])) {
                $contextSection .= "\nSUJET DE L'ANALYSE: {$contextData['analyseCible']['nom']} ({$contextData['analyseCible']['type']})\n";
            }
        }

        $prompt = <<<PROMPT
Tu es un expert agronome spécialisé en diagnostic agricole.
Analyse cette observation de terrain et fournis un diagnostic structuré.

OBSERVATION: {$observation}
{$contextSection}

Réponds UNIQUEMENT en JSON valide avec cette structure exacte:
{
  "condition": "nom de la maladie ou problème détecté",
  "confidence": "HIGH|MEDIUM|LOW",
  "symptoms": "symptômes observés",
  "treatment": "traitement recommandé",
  "prevention": "mesures préventives",
  "urgency": "Immédiat|Dans la semaine|Surveiller",
  "needsExpertConsult": true|false,
  "rawResponse": ""
}

Utilise le contexte de la ferme pour affiner ton diagnostic (maladies courantes dans la région, interactions avec d'autres cultures/animaux, etc.).
PROMPT;

        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => $this->model,
                    'messages'    => [
                        [
                            'role'    => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.3,
                    'max_tokens'  => 1024,
                ],
                'timeout' => 30,
            ]);

            $data    = $response->toArray();
            $content = $data['choices'][0]['message']['content'] ?? '{}';

            // Clean markdown code blocks if present
            $content = preg_replace('/```json\s*/i', '', $content);
            $content = preg_replace('/```\s*/i', '', $content);
            $content = trim($content);

            $parsed = json_decode($content, true);

            if (!$parsed) {
                return $this->errorResult('Réponse IA invalide', $content);
            }

            $parsed['rawResponse'] = $content;
            return DiagnosisResult::fromArray($parsed);

        } catch (\Throwable $e) {
            return $this->errorResult('Erreur API: ' . $e->getMessage());
        }
    }

    // ─── Vision Diagnostic (Image URL) ───────────────────────────────

    public function generateVisionDiagnostic(string $imageUrl, array $contextData = []): DiagnosisResult
    {
        // Build context section if data available
        $contextSection = '';
        if (!empty($contextData['ferme'])) {
            $contextSection .= "\nCONTEXTE DE LA FERME:\n";
            $contextSection .= "- Ferme: {$contextData['ferme']['nom']}\n";
            if ($contextData['ferme']['lieu']) {
                $contextSection .= "- Lieu: {$contextData['ferme']['lieu']}\n";
            }
            
            // Add related plantes
            if (!empty($contextData['plantes'])) {
                $contextSection .= "\nAutres plantes dans cette ferme:\n";
                foreach (array_slice($contextData['plantes'], 0, 5) as $plante) {
                    $contextSection .= "- {$plante['nom']}";
                    if ($plante['type']) {
                        $contextSection .= " (sol: {$plante['type']})";
                    }
                    $contextSection .= "\n";
                }
            }
            
            // Add related animaux
            if (!empty($contextData['animaux'])) {
                $contextSection .= "\nAutres animaux dans cette ferme:\n";
                foreach (array_slice($contextData['animaux'], 0, 5) as $animal) {
                    $contextSection .= "- {$animal['espece']}";
                    if ($animal['race']) {
                        $contextSection .= " (race: {$animal['race']})";
                    }
                    if ($animal['etat']) {
                        $contextSection .= " [état: {$animal['etat']}]";
                    }
                    $contextSection .= "\n";
                }
            }
            
            // Add what's being analyzed
            if (!empty($contextData['analyseCible']['type'])) {
                $contextSection .= "\nSUJET DE L'ANALYSE: {$contextData['analyseCible']['nom']} ({$contextData['analyseCible']['type']})\n";
            }
        }

        $prompt = <<<PROMPT
Tu es un expert agronome et vétérinaire agricole. Analyse cette image et identifie ce qui est représenté.

Étape 1: DÉTECTE CE QUI EST DANS L'IMAGE
- Plante/culture (légume, fruits, céréales, etc.)
- Animal (bétail, volaille, etc.)

Étape 2: ANALYSE ADAPTÉE

Si c'est une PLANTES/COLLECTION:
- Analyse les maladies foliaires, carences nutritionnelles, problèmes de racines
- Évalue les symptômes sur les feuilles, tiges, fruits
- Recommande traitements phytosanitaires, améliorations culturelles

Si c'est un ANIMAL:
- Évalue l'état de santé général, apparence physique
- Identifie les problèmes de peau, de la fleece, de la salive, etc.
- Recommande soins vétérinaires, traitements, pronostics
- Précise si consultation vétérinaire urgent nécessaire

{$contextSection}

Réponds UNIQUEMENT en JSON valide avec cette structure exacte:
{
  "subject_type": "plant|animal",
  "condition": "condition détectée ou "Santé normale"",
  "confidence": "HIGH|MEDIUM|LOW",
  "symptoms": "symptômes observés détaillés",
  "treatment": "traitement recommandé",
  "prevention": "mesures préventives",
  "urgency": "Immédiat|Dans la semaine|Surveiller",
  "needsExpertConsult": true|false,
  "rawResponse": ""
}

Contrôle de qualité: Vérifie que le JSON est bien formé et que tous les champs sont présents.
PROMPT;

        try {
            // Resolve image URL to a format suitable for Groq vision API
            $resolvedUrl = $this->resolveImageUrl($imageUrl);

            // Validate that we have a valid image URL
            if (empty($resolvedUrl) || $resolvedUrl === $imageUrl) {
                return $this->errorResult('Impossible de résoudre l\'URL de l\'image: ' . $imageUrl);
            }

            // Build vision message with proper image_url format for Groq
            $messages = [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $resolvedUrl,
                            ],
                        ],
                    ],
                ],
            ];

            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
                'json' => [
                    'model'    => self::VISION_MODEL,
                    'messages' => $messages,
                    'temperature' => 0.3,
                    'max_tokens'  => 1024,
                ],
                'timeout' => 60,
            ]);

            $data    = $response->toArray();
            $content = $data['choices'][0]['message']['content'] ?? '{}';

            $content = preg_replace('/```json\s*/i', '', $content);
            $content = preg_replace('/```\s*/i', '', $content);
            $content = trim($content);

            $parsed = json_decode($content, true);

            if (!$parsed) {
                return $this->errorResult('Réponse vision IA invalide: ' . $content);
            }

            $parsed['rawResponse'] = $content;
            return DiagnosisResult::fromArray($parsed);

        } catch (\Throwable $e) {
            $errorDetails = $e->getMessage();
            if (method_exists($e, 'getResponse')) {
                $response = $e->getResponse();
                if ($response) {
                    $errorDetails .= ' | Response: ' . $response->getContent(false);
                }
            }
            return $this->errorResult('Erreur Vision API: ' . $errorDetails);
        }
    }

    // ─── Executive Summary ────────────────────────────────────────────

    public function generateExecutiveSummary(
        string $farmName,
        int $totalAnalyses,
        int $totalConseils,
        array $priorityStats
    ): string {
        $statsText = "Haute: {$priorityStats['HAUTE']}, "
                   . "Moyenne: {$priorityStats['MOYENNE']}, "
                   . "Basse: {$priorityStats['BASSE']}";

        $prompt = <<<PROMPT
Tu es un expert agronome. Génère un résumé exécutif professionnel en français 
pour le rapport de la ferme "{$farmName}".

Données:
- Total analyses: {$totalAnalyses}
- Total conseils: {$totalConseils}
- Répartition priorités: {$statsText}

Écris un paragraphe de 3-4 phrases, professionnel et actionnable.
PROMPT;

        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => $this->model,
                    'messages'    => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.5,
                    'max_tokens'  => 256,
                ],
                'timeout' => 20,
            ]);

            $data = $response->toArray();
            return $data['choices'][0]['message']['content']
                ?? 'Résumé non disponible.';

        } catch (\Throwable $e) {
            return 'Erreur lors de la génération du résumé: ' . $e->getMessage();
        }
    }

    // ─── Private Helpers ──────────────────────────────────────────────

    private function errorResult(string $message, string $raw = ''): DiagnosisResult
    {
        return DiagnosisResult::fromArray([
            'condition'          => 'Erreur de diagnostic',
            'confidence'         => 'LOW',
            'symptoms'           => [$message],
            'treatment'          => 'Veuillez réessayer ou consulter un expert.',
            'prevention'         => '',
            'urgency'            => 'Surveiller',
            'needsExpertConsult' => true,
            'rawResponse'        => $raw,
            'success'            => false,
            'errorMessage'       => 'Failed to get AI diagnosis',
        ]);
    }

    /**
     * Diagnose plant disease from an image (wrapper for generateVisionDiagnostic).
     * This method is used by tests and provides a simpler interface.
     *
     * @param string $imageUrl URL or path to the image
     * @return DiagnosisResult
     */
    public function diagnosePlantDisease(string $imageUrl): DiagnosisResult
    {
        return $this->generateVisionDiagnostic($imageUrl);
    }

    /**
     * Resolve image URL to a format suitable for Groq vision API.
     * - Returns as-is if already a full URL (http/https) or base64 data URI
     * - Converts local file paths to base64 data URIs (Windows or Unix)
     */
    private function resolveImageUrl(string $imageUrl): string
    {
        // Already a full URL
        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return $imageUrl;
        }

        // Already a base64 data URI
        if (str_starts_with($imageUrl, 'data:image/')) {
            return $imageUrl;
        }

        // Check if it's a Windows absolute path (e.g., C:\Users\... or C:/Users/...)
        // Use string detection instead of regex to avoid regex issues
        if (strlen($imageUrl) >= 3 && ctype_alpha($imageUrl[0]) && $imageUrl[1] === ':' && ($imageUrl[2] === '\\' || $imageUrl[2] === '/')) {
            // Normalize path separators
            $normalizedPath = str_replace('/', '\\', $imageUrl);

            error_log('[GroqService] Windows path detected: ' . $normalizedPath);
            error_log('[GroqService] File exists: ' . (file_exists($normalizedPath) ? 'YES' : 'NO'));
            error_log('[GroqService] Is file: ' . (is_file($normalizedPath) ? 'YES' : 'NO'));
            error_log('[GroqService] Is readable: ' . (is_readable($normalizedPath) ? 'YES' : 'NO'));

            // Try to find the file - check various path variations
            $pathsToTry = [
                $normalizedPath,
                str_replace('\\\\', '\\', $normalizedPath),
                str_replace('\\', '/', $normalizedPath),
                $imageUrl,
            ];

            foreach ($pathsToTry as $path) {
                $path = trim($path);
                if (!empty($path) && file_exists($path) && is_file($path)) {
                    error_log('[GroqService] Found file at: ' . $path);
                    return $this->convertFileToBase64($path);
                }
            }

            // Try to find file in common locations (Downloads, Pictures, etc.)
            $filename = basename($normalizedPath);
            $homeDir = getenv('USERPROFILE') ?: (getenv('HOME') ?: '/tmp');
            $username = getenv('USERNAME') ?: 'sliti';

            $commonPaths = [
                $homeDir . '\\Downloads\\' . $filename,
                $homeDir . '\\Pictures\\' . $filename,
                $homeDir . '\\Desktop\\' . $filename,
                'C:\\Users\\' . $username . '\\Downloads\\' . $filename,
            ];

            foreach ($commonPaths as $path) {
                if (file_exists($path) && is_file($path)) {
                    error_log('[GroqService] Found file in common path: ' . $path);
                    return $this->convertFileToBase64($path);
                }
            }

            error_log('[GroqService] Could not find file, returning original path');
            return $imageUrl;
        } else {
            error_log('[GroqService] Not a Windows path, checking if relative path: ' . $imageUrl);
        }

        // Unix-style path (relative or absolute)
        // Remove leading slash and prepend to project root
        $localPath = __DIR__ . '/../../public' . $imageUrl;

        if (!file_exists($localPath)) {
            // Try alternative path resolution
            $projectRoot = __DIR__ . '/../..';
            $localPath = $projectRoot . '/public' . $imageUrl;
        }

        if (file_exists($localPath) && is_file($localPath)) {
            return $this->convertFileToBase64($localPath);
        }

        // Return original if we can't resolve it
        return $imageUrl;
    }

    /**
     * Convert a local file to base64 data URI
     */
    private function convertFileToBase64(string $filePath): string
    {
        if (!file_exists($filePath) || !is_file($filePath)) {
            return '';
        }

        $mimeType = mime_content_type($filePath);
        if (!$mimeType) {
            $mimeType = 'image/jpeg';
        }

        $imageContent = file_get_contents($filePath);
        if ($imageContent === false) {
            return '';
        }

        $base64Image = base64_encode($imageContent);
        return 'data:' . $mimeType . ';base64,' . $base64Image;
    }
}
