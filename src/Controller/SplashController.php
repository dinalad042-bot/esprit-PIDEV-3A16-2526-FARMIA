<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SplashController extends AbstractController
{
    #[Route('/splash/transition', name: 'splash_transition')]
    public function transition(Request $request): Response
    {
        $target = $request->query->get('target', $this->generateUrl('app_login'));
        $type = $request->query->get('type', 'login');

        return $this->render('splash/transition.html.twig', [
            'target_url' => $target,
            'type' => $type
        ]);
    }

    #[Route('/splash/logout', name: 'splash_logout')]
    public function logoutTransition(): Response
    {
        return $this->render('splash/transition.html.twig', [
            'target_url' => $this->generateUrl('app_login'),
            'type' => 'logout'
        ]);
    }
}
