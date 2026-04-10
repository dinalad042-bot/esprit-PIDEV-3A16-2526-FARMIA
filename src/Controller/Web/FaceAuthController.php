<?php

namespace App\Controller\Web;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\LoginSuccessHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\RouterInterface;
use App\Service\UserLogService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Route('/auth/face')]
class FaceAuthController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private Security $security,
        private RouterInterface $router,
        private UserLogService $userLogService,
        #[Autowire('%env(string:PYTHON_API_URL)%')]
        private string $pythonApiUrl = 'http://127.0.0.1:5000'
    ) {}

    private function checkApiHealth(): bool
    {
        try {
            $response = $this->httpClient->request('GET', rtrim($this->pythonApiUrl, '/') . '/health', [
                'timeout' => 2 // timeout court pour fail vite
            ]);
            $data = $response->toArray(false);
            return isset($data['success']) && $data['success'] === true;
        } catch (\Exception $e) {
            return false;
        }
    }

    #[Route('/register', name: 'app_face_register', methods: ['POST'])]
    public function registerFace(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['success' => false, 'error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['image'])) {
            return new JsonResponse(['success' => false, 'error' => 'Image requise'], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->checkApiHealth()) {
            return new JsonResponse([
                'success' => false, 
                'error' => "L'API de reconnaissance faciale est momentanément indisponible.\nVeuillez démarrer le serveur Python puis réessayer."
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        try {
            // Appel à l'API Python Flask locale
            $response = $this->httpClient->request('POST', rtrim($this->pythonApiUrl, '/') . '/api/enroll', [
                'json' => [
                    'user_id' => (string) $user->getId(),
                    'image' => $data['image']
                ]
            ]);

            $result = $response->toArray(false);

            if (isset($result['message']) && !$result['success']) {
                return new JsonResponse(['success' => false, 'error' => 'Erreur analyse: ' . $result['message']], Response::HTTP_BAD_REQUEST);
            }

            if (isset($result['success']) && $result['success'] === true) {
                // Le modèle est entraîné côté python, plus de faceDescriptor local à sauver
                $user->setFaceAuthEnabled(true);
                $user->setFaceRegisteredAt(new \DateTime());

                $this->em->flush();
                
                // Log the action
                $this->userLogService->log($user, 'FACE_ENROLL', 'SUCCESS');

                return new JsonResponse(['success' => true]);
            }

            return new JsonResponse(['success' => false, 'error' => 'Réponse inattendue de l\'API IA'], Response::HTTP_INTERNAL_SERVER_ERROR);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => 'Erreur de connexion à l\'API Python : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/login', name: 'app_face_login', methods: ['POST'])]
    public function loginFace(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['image'])) {
            return new JsonResponse(['success' => false, 'error' => 'Image requise'], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->checkApiHealth()) {
            return new JsonResponse([
                'success' => false, 
                'error' => "L'API de reconnaissance faciale est momentanément indisponible.\nVeuillez démarrer le serveur Python puis réessayer."
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        try {
            $response = $this->httpClient->request('POST', rtrim($this->pythonApiUrl, '/') . '/api/recognize', [
                'json' => [
                    'image' => $data['image']
                ],
                'timeout' => 10
            ]);

            $result = $response->toArray(false);

            if (isset($result['message']) && !$result['success']) {
                return new JsonResponse(['success' => false, 'error' => $result['message']], Response::HTTP_BAD_REQUEST);
            }

            if (isset($result['success']) && $result['success'] === true && isset($result['user_id'])) {
                // Trouver l'utilisateur correspondant
                $matchedUser = $this->userRepository->find($result['user_id']);
                if (!$matchedUser) {
                    return new JsonResponse(['success' => false, 'error' => 'Utilisateur reconnu mais introuvable en base'], Response::HTTP_NOT_FOUND);
                }

                // Authentifier l'utilisateur
                $this->security->login($matchedUser, 'security.authenticator.form_login.main');

                // Log login
                $this->userLogService->log($matchedUser, 'LOGIN_FACE', 'SUCCESS');

                // Déterminer la redirection exactement comme LoginSuccessHandler
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
                    'type' => 'login'
                ]);

                return new JsonResponse([
                    'success' => true,
                    'redirect' => $splashUrl
                ]);
            }

            return new JsonResponse(['success' => false, 'error' => 'Échec de la reconnaissance'], Response::HTTP_UNAUTHORIZED);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => 'Erreur de connexion à l\'API IA : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
