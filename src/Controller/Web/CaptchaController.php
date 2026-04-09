<?php

namespace App\Controller\Web;

use App\Service\CaptchaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CaptchaController extends AbstractController
{
    #[Route('/captcha/image', name: 'captcha_image')]
    public function image(CaptchaService $captchaService, RequestStack $requestStack): Response
    {
        // Générer un nouveau texte CAPTCHA
        $text = $captchaService->generateText();

        // Stocker en session (en gardant la casse exacte)
        $session = $requestStack->getSession();
        $session->set('_captcha_code', $text);

        // Générer l'image (PNG si GD, sinon SVG)
        $captchaData = $captchaService->generate($text);

        return new Response($captchaData['content'], 200, [
            'Content-Type'  => $captchaData['type'],
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }
}
