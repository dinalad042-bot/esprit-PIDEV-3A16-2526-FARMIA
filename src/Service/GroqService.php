<?php

namespace App\Service;

use App\DTO\DiagnosisResult;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GroqService
{
    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
        private string $model = 'llama-3.2-11b-vision-preview'
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
Tu es un expert agronome. Analyse cette image de plante ou de culture agricole.
Identifie toute maladie, carence, ou problème visible.
{$contextSection}
Réponds UNIQUEMENT en JSON valide avec cette structure:
{
  "condition": "condition détectée",
  "confidence": "HIGH|MEDIUM|LOW",
  "symptoms": "symptômes visuels observés",
  "treatment": "traitement recommandé",
  "prevention": "prévention",
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
                    'model'    => $this->model,
                    'messages' => [
                        [
                            'role'    => 'user',
                            'content' => $prompt . "\n\nAnalyse cette image: " . $imageUrl,
                        ],
                    ],
                    'temperature' => 0.3,
                    'max_tokens'  => 1024,
                ],
                'timeout' => 30,
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
        ]);
    }
}
