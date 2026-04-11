<?php

namespace App\Controller;

use App\Service\OpenAIChatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChatbotController extends AbstractController
{
    #[Route('/user/chatbot/message', name: 'app_chatbot_message', methods: ['POST'])]
    public function message(Request $request, OpenAIChatService $chatService): JsonResponse
    {
        if (!$this->getUser()) {
            return new JsonResponse(['error' => 'Non autorisé. Veuillez vous connecter.'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';

        if (empty(trim($message))) {
            return new JsonResponse(['error' => 'Message vide'], 400);
        }

        $botResponse = $chatService->generateResponse(trim($message));

        return new JsonResponse([
            'response' => $botResponse,
        ]);
    }
}
