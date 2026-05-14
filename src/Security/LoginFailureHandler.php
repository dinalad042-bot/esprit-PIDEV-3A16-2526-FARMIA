<?php

namespace App\Security;

use App\Repository\UserRepository;
use App\Service\UserLogService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class LoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly UserRepository $userRepository,
        private readonly UserLogService $userLogService
    ) {}

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        $email = $request->request->get('_username');
        
        $user = null;
        if ($email) {
            $user = $this->userRepository->findOneBy(['email' => $email]);
        }

        if ($exception instanceof \Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException) {
            $message = $exception->getMessage();
        } elseif ($exception instanceof InvalidCsrfTokenException) {
            $message = 'Session expirée ou jeton CSRF invalide.';
        } elseif (!$user) {
            $message = 'Compte introuvable';
        } else {
            $message = 'Mot de passe incorrect';
            // Log the failure
            $this->userLogService->log($user, 'LOGIN', 'FAILED');
        }

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add('error', $message);
        
        return new RedirectResponse($this->router->generate('app_login'));
    }
}