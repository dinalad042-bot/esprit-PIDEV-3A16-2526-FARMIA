<?php

namespace App\Controller\Api;

use App\Service\AuthService;
use App\Service\UserLogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', format: 'json')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly UserLogService $userLogService,
    ) {}

    // ─── POST /api/signup ────────────────────────────────────────────────────

    #[Route('/signup', name: 'api_signup', methods: ['POST'])]
    public function signup(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->authService->signup($data);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        $this->userLogService->log($user, 'SIGNUP', 'SUCCESS');

        return $this->json([
            'message' => 'User registered successfully.',
            'user'    => $this->serializeUser($user),
        ], Response::HTTP_CREATED);
    }

    // ─── POST /api/login ─────────────────────────────────────────────────────

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email    = $data['email']    ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            return $this->json(['error' => 'Email and password are required.'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->authService->login($email, $password);

        if (!$user) {
            return $this->json(['error' => 'Invalid credentials.'], Response::HTTP_UNAUTHORIZED);
        }

        $this->userLogService->log($user, 'LOGIN', 'SUCCESS');

        return $this->json([
            'message' => 'Login successful.',
            'user'    => $this->serializeUser($user),
        ]);
    }

    // ─── Shared serializer ───────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function serializeUser(\App\Entity\User $user): array
    {
        return [
            'id'        => $user->getId(),
            'nom'       => $user->getNom(),
            'prenom'    => $user->getPrenom(),
            'email'     => $user->getEmail(),
            'role'      => $user->getRole(),
            'cin'       => $user->getCin(),
            'telephone' => $user->getTelephone(),
            'adresse'   => $user->getAdresse(),
            'image_url' => $user->getImageUrl(),
        ];
    }
}