<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class OpenAIChatService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        #[Autowire('%env(resolve:OPENAI_API_KEY)%')]
        private string $openAiKey,
    ) {
    }

    public function generateResponse(string $userMessage): string
    {
        if (empty($this->openAiKey)) {
            return "Configuration OpenAI manquante. Veuillez contacter l'administrateur système.";
        }

        $systemPrompt = <<<PROMPT
Tu es l'assistant IA officiel de FarmAI. Tu aides les utilisateurs concernant :
- le compte
- le profil
- la connexion
- le mot de passe oublié
- la reconnaissance faciale

Règles :
1. Réponds uniquement sur FarmAI et ses fonctionnalités utilisateur.
2. Ne divulgue aucune information sensible.
3. Si la question est hors sujet, réponds poliment que tu es limité à l'assistance FarmAI.
4. Reste clair, professionnel et concis.
PROMPT;

        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openAiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'max_tokens' => 450,
                    'temperature' => 0.5,
                ],
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);

            if ($statusCode !== 200) {
                $this->logger->error('Erreur API OpenAI', [
                    'status_code' => $statusCode,
                    'response' => $content,
                ]);

                return $this->getFriendlyErrorMessage($statusCode);
            }

            $data = json_decode($content, true);

            if (!isset($data['choices'][0]['message']['content'])) {
                $this->logger->error('Réponse OpenAI invalide', [
                    'response' => $content,
                ]);

                return "Le service d'assistance est momentanément indisponible. Veuillez réessayer plus tard.";
            }

            return trim($data['choices'][0]['message']['content']);
        } catch (ExceptionInterface $e) {
            $this->logger->error('Erreur HTTP OpenAI', [
                'message' => $e->getMessage(),
            ]);

            return "Le service d'assistance est momentanément indisponible. Veuillez réessayer plus tard.";
        } catch (\Throwable $e) {
            $this->logger->error('Erreur système OpenAI', [
                'message' => $e->getMessage(),
            ]);

            return "Le service d'assistance est momentanément indisponible. Veuillez réessayer plus tard.";
        }
    }

    private function getFriendlyErrorMessage(int $statusCode): string
    {
        return match ($statusCode) {
            401, 403 => "La configuration OpenAI est invalide. Veuillez contacter l'administrateur.",
            429 => "L'assistant intelligent est temporairement limité. Veuillez réessayer plus tard.",
            500, 502, 503, 504 => "Le service d'assistance est momentanément indisponible. Veuillez réessayer plus tard.",
            default => "Une erreur est survenue avec le service d'assistance. Veuillez réessayer plus tard.",
        };
    }
}