<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

/**
 * Vérifie le CAPTCHA AVANT que Symfony ne vérifie les credentials.
 * 
 * Priorité 512 = s'exécute avant le CredentialsCheckListener de Symfony (priorité 0).
 * Si le CAPTCHA est faux, on lance une exception d'authentification
 * qui sera traitée par le LoginFailureHandler existant.
 */
#[AsEventListener(event: CheckPassportEvent::class, priority: 512)]
class CaptchaLoginListener
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {}

    public function __invoke(CheckPassportEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return;
        }

        // N'agir que sur les requêtes POST vers /login
        if ($request->getMethod() !== 'POST' || $request->getPathInfo() !== '/login') {
            return;
        }

        $session = $request->getSession();
        $expectedCode = $session->get('_captcha_code', '');
        $submittedCode = strtolower(trim((string) $request->request->get('_captcha', '')));

        // Supprimer le code de la session pour empêcher la réutilisation
        $session->remove('_captcha_code');

        if (empty($submittedCode) || $submittedCode !== $expectedCode) {
            throw new CustomUserMessageAuthenticationException(
                'Code CAPTCHA incorrect. Veuillez réessayer.'
            );
        }
    }
}
