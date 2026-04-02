<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Redirige vers le tableau de bord si l'utilisateur est connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard_default');
        }

        // Sinon, redirige vers la page de connexion
        return $this->redirectToRoute('app_login');
    }
}
