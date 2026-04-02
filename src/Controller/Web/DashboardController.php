<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard_default')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) return $this->redirectToRoute('admin_dashboard');
        if (in_array('ROLE_EXPERT', $roles, true)) return $this->redirectToRoute('dashboard_expert');
        if (in_array('ROLE_AGRICOLE', $roles, true)) return $this->redirectToRoute('dashboard_agricole');
        if (in_array('ROLE_FOURNISSEUR', $roles, true)) return $this->redirectToRoute('dashboard_fournisseur');

        // Fallback générique
        return $this->render('dashboard/default.html.twig', [
            'user' => $user,
        ]);
    }


    #[Route('/expert/dashboard', name: 'dashboard_expert')]
    #[IsGranted('ROLE_EXPERT')]
    public function expert(): Response
    {
        return $this->render('portal/expert/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/agricole/dashboard', name: 'dashboard_agricole')]
    #[IsGranted('ROLE_AGRICOLE')]
    public function agricole(): Response
    {
        return $this->render('portal/agricole/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/fournisseur/dashboard', name: 'dashboard_fournisseur')]
    #[IsGranted('ROLE_FOURNISSEUR')]
    public function fournisseur(): Response
    {
        return $this->render('portal/fournisseur/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }
}
