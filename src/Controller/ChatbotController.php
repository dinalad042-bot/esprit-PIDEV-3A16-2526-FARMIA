<?php

namespace App\Controller;

use App\DTO\ChatRequestDTO;
use App\Service\GroqChatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChatbotController extends AbstractController
{
    /**
     * Main chat endpoint — receives a user message, validates it via DTO,
     * delegates to GroqChatService, and returns the AI response as JSON.
     */
    #[Route('/user/chatbot/message', name: 'app_chatbot_message', methods: ['POST'])]
    public function message(
        Request $request,
        GroqChatService $chatService,
        ValidatorInterface $validator,
    ): JsonResponse {
        // ── Auth guard ──────────────────────────────────────────────────────
        if (!$this->getUser()) {
            return new JsonResponse(['error' => 'Non autorisé. Veuillez vous connecter.'], 403);
        }

        // ── Parse & validate via DTO ────────────────────────────────────────
        $data = json_decode($request->getContent(), true) ?? [];
        $dto = ChatRequestDTO::fromArray($data);

        if ($dto === null) {
            return new JsonResponse(['error' => 'Message vide.'], 400);
        }

        $violations = $validator->validate($dto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['error' => implode(' ', $errors)], 400);
        }

        // ── Call Groq AI ────────────────────────────────────────────────────
        $botResponse = $chatService->generateResponse($dto->message);

        return new JsonResponse([
            'response' => $botResponse,
        ]);
    }

    /**
     * Optional endpoint to clear chatbot conversation history.
     */
    #[Route('/user/chatbot/clear', name: 'app_chatbot_clear', methods: ['POST'])]
    public function clearHistory(GroqChatService $chatService): JsonResponse
    {
        if (!$this->getUser()) {
            return new JsonResponse(['error' => 'Non autorisé.'], 403);
        }

        $chatService->clearHistory();

        return new JsonResponse(['status' => 'ok', 'message' => 'Historique effacé.']);
    }
}
