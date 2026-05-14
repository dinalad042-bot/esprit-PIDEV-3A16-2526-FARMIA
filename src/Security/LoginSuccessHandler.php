<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

use App\Service\UserLogService;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly UserLogService $userLogService
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        /** @var \App\Entity\User $user */
        $user = $token->getUser();
        
        // Log the successful login
        $this->userLogService->log($user, 'LOGIN', 'SUCCESS');

        $roles = $token->getRoleNames();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            $url = $this->router->generate('admin_dashboard');
        } elseif (in_array('ROLE_EXPERT', $roles, true)) {
            $url = $this->router->generate('dashboard_expert');
        } elseif (in_array('ROLE_AGRICOLE', $roles, true)) {
            $url = $this->router->generate('dashboard_agricole');
        } elseif (in_array('ROLE_FOURNISSEUR', $roles, true)) {
            $url = $this->router->generate('dashboard_fournisseur');
        } else {
            // Fallback pour les utilisateurs lambda sans rôles spécifiques ci-dessus
            $url = $this->router->generate('dashboard_default');
        }

        // Rediriger vers l'écran de splash avec la cible finale
        $splashUrl = $this->router->generate('splash_transition', [
            'target' => $url,
            'type' => 'login'
        ]);

        return new RedirectResponse($splashUrl);
    }
}
