<?php

namespace App\Controller\Api;

use App\Service\UserLogService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users', format: 'json')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserLogService $userLogService,
    ) {}

    // ─── GET /api/users ──────────────────────────────────────────────────────

    #[Route('', name: 'api_users_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $users = $this->userService->findAll();

        return $this->json([
            'data' => array_map([$this, 'serialize'], $users),
        ]);
    }

    // ─── GET /api/users/{id} ─────────────────────────────────────────────────

    #[Route('/{id}', name: 'api_users_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (!$user) {
            return $this->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['data' => $this->serialize($user)]);
    }

    // ─── POST /api/users ─────────────────────────────────────────────────────

    #[Route('', name: 'api_users_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(['error' => 'email and password are required.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $user = $this->userService->create($data);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        $this->userLogService->log($user, 'CREATE', 'SUCCESS');

        return $this->json(['data' => $this->serialize($user)], Response::HTTP_CREATED);
    }

    // ─── PUT /api/users/{id} ─────────────────────────────────────────────────

    #[Route('/{id}', name: 'api_users_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (!$user) {
            return $this->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userService->update($user, $data);

        $this->userLogService->log($user, 'UPDATE', 'SUCCESS');

        return $this->json(['data' => $this->serialize($user)]);
    }

    // ─── DELETE /api/users/{id} ──────────────────────────────────────────────

    #[Route('/{id}', name: 'api_users_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->userService->findById($id);

        if (!$user) {
            return $this->json(['error' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }

        $this->userLogService->log($user, 'DELETE', 'SUCCESS');

        $this->userService->delete($user);

        return $this->json(['message' => 'User deleted.'], Response::HTTP_OK);
    }

    // ─── Serializer ──────────────────────────────────────────────────────────

    private function serialize(\App\Entity\User $user): array
    {
        return [
            'id'        => $user->getId(),
            'nom'       => $user->getNom(),
            'prenom'    => $user->getPrenom(),
            'email'     => $user->getEmail(),
            'role'      => $user->getRole(),
            'telephone' => $user->getTelephone(),
            'adresse'   => $user->getAdresse(),
            'image_url' => $user->getImageUrl(),
        ];
    }
}
