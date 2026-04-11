<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Service layer for communicating with the Groq Cloud API (LLaMA / Mixtral models).
 *
 * Features:
 *  - Secure API key injection via .env
 *  - Automatic retry on transient failures (502/503/504)
 *  - Conversation history stored in session for multi-turn context
 *  - Full Monolog logging for debugging and monitoring
 */
class GroqChatService
{
    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL = 'llama-3.3-70b-versatile';
    private const MAX_TOKENS = 1024;
    private const TEMPERATURE = 0.7;
    private const TIMEOUT = 30;
    private const MAX_RETRIES = 2;
    private const MAX_HISTORY_MESSAGES = 10; // keep last N exchanges in session

    private const SESSION_KEY = '_groq_chat_history';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private RequestStack $requestStack,
        #[Autowire('%env(resolve:GROQ_API_KEY)%')]
        private string $groqApiKey,
    ) {
    }

    // ─── Public API ─────────────────────────────────────────────────────────────

    /**
     * Generate an AI response from Groq for the given user message.
     * Maintains session-based conversation history for multi-turn context.
     */
    public function generateResponse(string $userMessage): string
    {
        if (empty($this->groqApiKey)) {
            $this->logger->error('Groq API key is not configured.');
            return "Configuration Groq manquante. Veuillez contacter l'administrateur système.";
        }

        // Build the messages array with system prompt + session history + new message
        $history = $this->getHistory();
        $messages = $this->buildMessages($history, $userMessage);

        // Call Groq API with retry logic
        $botReply = $this->callApiWithRetry($messages);

        // Persist the exchange in session history
        $this->appendToHistory($userMessage, $botReply);

        return $botReply;
    }

    /**
     * Clear the conversation history stored in the session.
     */
    public function clearHistory(): void
    {
        $session = $this->requestStack->getSession();
        $session->remove(self::SESSION_KEY);
    }

    // ─── Private Helpers ────────────────────────────────────────────────────────

    private function buildMessages(array $history, string $userMessage): array
    {
        $systemPrompt = <<<PROMPT
Tu es l'assistant IA officiel de FarmAI, propulsé par intelligence artificielle.
Tu aides les utilisateurs concernant :
- le compte et le profil
- la connexion et le mot de passe oublié
- la reconnaissance faciale
- les fonctionnalités de la plateforme agricole

Règles :
1. Réponds uniquement sur FarmAI et ses fonctionnalités.
2. Ne divulgue aucune information sensible ni technique interne.
3. Si la question est hors sujet, réponds poliment que tu es limité à l'assistance FarmAI.
4. Reste clair, professionnel et concis.
5. Réponds en français par défaut, sauf si l'utilisateur écrit dans une autre langue.
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Append session history for multi-turn context
        foreach ($history as $exchange) {
            $messages[] = ['role' => 'user', 'content' => $exchange['user']];
            $messages[] = ['role' => 'assistant', 'content' => $exchange['assistant']];
        }

        // Append the new user message
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }

    /**
     * Call the Groq API with automatic retry on transient server errors.
     */
    private function callApiWithRetry(array $messages): string
    {
        $lastException = null;

        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                if ($attempt > 0) {
                    $this->logger->info('Groq API retry attempt', ['attempt' => $attempt]);
                    // Exponential backoff: 500ms, 1000ms, ...
                    usleep($attempt * 500_000);
                }

                return $this->callApi($messages);
            } catch (\RuntimeException $e) {
                $lastException = $e;

                // Only retry on transient server errors
                if (!$this->isRetryableError($e)) {
                    $this->logger->error('Groq API non-retryable error', [
                        'message' => $e->getMessage(),
                    ]);
                    break;
                }

                $this->logger->warning('Groq API transient error, will retry', [
                    'attempt' => $attempt,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $lastException
            ? $this->getFriendlyErrorFromException($lastException)
            : "Le service d'assistance est momentanément indisponible. Veuillez réessayer plus tard.";
    }

    /**
     * Execute a single API call to Groq.
     *
     * @throws \RuntimeException on any failure (with status code in the message)
     */
    private function callApi(array $messages): string
    {
        try {
            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->groqApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => self::MODEL,
                    'messages' => $messages,
                    'max_tokens' => self::MAX_TOKENS,
                    'temperature' => self::TEMPERATURE,
                ],
                'timeout' => self::TIMEOUT,
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);

            if ($statusCode !== 200) {
                $this->logger->error('Groq API error response', [
                    'status_code' => $statusCode,
                    'response' => mb_substr($content, 0, 500),
                ]);
                throw new \RuntimeException("Groq API error: HTTP {$statusCode}", $statusCode);
            }

            $data = json_decode($content, true);

            if (!isset($data['choices'][0]['message']['content'])) {
                $this->logger->error('Groq API response missing expected fields', [
                    'response' => mb_substr($content, 0, 500),
                ]);
                throw new \RuntimeException('Invalid Groq API response structure', 0);
            }

            $botReply = trim($data['choices'][0]['message']['content']);

            $this->logger->info('Groq API call successful', [
                'model' => $data['model'] ?? self::MODEL,
                'usage' => $data['usage'] ?? [],
            ]);

            return $botReply;

        } catch (ExceptionInterface $e) {
            $this->logger->error('Groq HTTP client exception', [
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('HTTP client error: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Determine if an error is transient and worth retrying.
     */
    private function isRetryableError(\RuntimeException $e): bool
    {
        $code = $e->getCode();
        return in_array($code, [500, 502, 503, 504], true);
    }

    private function getFriendlyErrorFromException(\RuntimeException $e): string
    {
        $code = $e->getCode();

        return match (true) {
            $code === 401, $code === 403 => "La configuration de l'assistant IA est invalide. Veuillez contacter l'administrateur.",
            $code === 429 => "L'assistant intelligent est temporairement limité. Veuillez réessayer dans quelques instants.",
            $code >= 500 => "Le service d'assistance est momentanément indisponible. Veuillez réessayer plus tard.",
            default => "Une erreur est survenue avec l'assistant IA. Veuillez réessayer plus tard.",
        };
    }

    // ─── Session History ────────────────────────────────────────────────────────

    private function getHistory(): array
    {
        $session = $this->requestStack->getSession();
        return $session->get(self::SESSION_KEY, []);
    }

    private function appendToHistory(string $userMessage, string $assistantReply): void
    {
        $session = $this->requestStack->getSession();
        $history = $session->get(self::SESSION_KEY, []);

        $history[] = [
            'user' => $userMessage,
            'assistant' => $assistantReply,
        ];

        // Trim to prevent the session from growing indefinitely
        if (count($history) > self::MAX_HISTORY_MESSAGES) {
            $history = array_slice($history, -self::MAX_HISTORY_MESSAGES);
        }

        $session->set(self::SESSION_KEY, $history);
    }
}
