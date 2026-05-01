<?php

namespace App\Controller\Web;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\FaceEnrollmentService;
use App\Service\UserLogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/auth/face')]
class FaceAuthController extends AbstractController
{
    public function __construct(
        private readonly FaceEnrollmentService  $faceEnrollmentService,
        private readonly UserRepository         $userRepository,
        private readonly Security               $security,
        private readonly RouterInterface        $router,
        private readonly UserLogService         $userLogService,
        private readonly HttpClientInterface    $httpClient,
        #[Autowire('%env(string:PYTHON_API_URL)%')]
        private readonly string $pythonApiUrl = 'http://127.0.0.1:5000'
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // POST /auth/face/register — Enrôlement du visage
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/register', name: 'app_face_register', methods: ['POST'])]
    public function registerFace(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(
                ['success' => false, 'error' => 'Non authentifié'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['image'])) {
            return new JsonResponse(
                ['success' => false, 'error' => 'Image requise'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Déléguer tout au service d'enrôlement
        $result = $this->faceEnrollmentService->enrollFace($user, $data['image']);

        if (!$result['success']) {
            return new JsonResponse(
                ['success' => false, 'error' => $result['message']],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Log l'action
        $this->userLogService->log($user, 'FACE_ENROLL', 'SUCCESS');

        $face = $result['face'];

        return new JsonResponse([
            'success'       => true,
            'message'       => $result['message'],
            'face_id'       => $face->getId(),
            'samples_count' => $face->getSamplesCount(),
            'enrolled_at'   => $face->getEnrolledAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /auth/face/login — Reconnaissance faciale
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/login', name: 'app_face_login', methods: ['POST'])]
    public function loginFace(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['image'])) {
            return new JsonResponse(
                ['success' => false, 'error' => 'Image requise'],
                Response::HTTP_BAD_REQUEST
            );
        }


        try {
            // Appel direct à l'API Python pour la reconnaissance
            $response = $this->httpClient->request('POST', rtrim($this->pythonApiUrl, '/') . '/api/recognize', [
                'json'    => ['image' => $data['image']],
                'timeout' => 10,
            ]);

            $result = $response->toArray(false);

            if (!isset($result['success']) || $result['success'] !== true || !isset($result['user_id'])) {
                $msg = $result['message'] ?? 'Visage non reconnu.';
                return new JsonResponse(['success' => false, 'error' => $msg], Response::HTTP_UNAUTHORIZED);
            }

            // Trouver l'utilisateur en base
            $matchedUser = $this->userRepository->find($result['user_id']);
            if (!$matchedUser) {
                return new JsonResponse(
                    ['success' => false, 'error' => 'Utilisateur reconnu mais introuvable en base'],
                    Response::HTTP_NOT_FOUND
                );
            }

            // ── Vérification critique : l'utilisateur doit avoir un visage actif en BDD ──
            if (!$this->faceEnrollmentService->hasFaceAuth($matchedUser)) {
                return new JsonResponse(
                    ['success' => false, 'error' => 'Authentification faciale non activée pour ce compte.'],
                    Response::HTTP_FORBIDDEN
                );
            }

            // Authentifier via Symfony Security
            $this->security->login($matchedUser, 'security.authenticator.form_login.main');

            // Log
            $this->userLogService->log($matchedUser, 'LOGIN_FACE', 'SUCCESS');

            // Redirection selon le rôle
            $roles = $matchedUser->getRoles();
            if (in_array('ROLE_ADMIN', $roles, true)) {
                $url = $this->router->generate('admin_dashboard');
            } elseif (in_array('ROLE_EXPERT', $roles, true)) {
                $url = $this->router->generate('dashboard_expert');
            } elseif (in_array('ROLE_AGRICOLE', $roles, true)) {
                $url = $this->router->generate('dashboard_agricole');
            } elseif (in_array('ROLE_FOURNISSEUR', $roles, true)) {
                $url = $this->router->generate('dashboard_fournisseur');
            } else {
                $url = $this->router->generate('dashboard_default');
            }

            $splashUrl = $this->router->generate('splash_transition', [
                'target' => $url,
                'type'   => 'login',
            ]);

            return new JsonResponse(['success' => true, 'redirect' => $splashUrl]);

        } catch (\Exception $e) {
            return new JsonResponse(
                ['success' => false, 'error' => 'Erreur de connexion à l\'API IA : ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE /auth/face/delete — Suppression du visage
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/delete', name: 'app_face_delete', methods: ['DELETE', 'POST'])]
    public function deleteFace(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(
                ['success' => false, 'error' => 'Non authentifié'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $result = $this->faceEnrollmentService->deleteFace($user);

        if (!$result['success']) {
            return new JsonResponse($result, Response::HTTP_NOT_FOUND);
        }

        $this->userLogService->log($user, 'FACE_DELETE', 'SUCCESS');

        return new JsonResponse($result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /auth/face/status — Statut du visage (pour le frontend)
    // ─────────────────────────────────────────────────────────────────────────

    #[Route('/status', name: 'app_face_status', methods: ['GET'])]
    public function faceStatus(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(
                ['success' => false, 'error' => 'Non authentifié'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $face = $this->faceEnrollmentService->getActiveFace($user);

        return new JsonResponse([
            'success'       => true,
            'has_face'      => $face !== null,
            'face_id'       => $face?->getId(),
            'samples_count' => $face?->getSamplesCount(),
            'enrolled_at'   => $face?->getEnrolledAt()?->format('Y-m-d H:i:s'),
            'updated_at'    => $face?->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'is_active'     => $face?->isActive() ?? false,
        ]);
    }
}
