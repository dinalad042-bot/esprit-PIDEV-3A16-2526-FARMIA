<?php

namespace App\Controller\ERP;

use App\Repository\ERP\MatiereRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/erp', name: 'erp_')]
class ERPDashboardController extends AbstractController
{
    #[Route('', name: 'dashboard')]
    public function index(MatiereRepository $matiereRepo): Response
    {
        $critiques = $matiereRepo->findStockCritique();

        $user  = $this->getUser();
        $roles = $user ? $user->getRoles() : [];

        if (in_array('ROLE_AGRICOLE', $roles, true)) {
            return $this->render('erp/dashboard/agricole.html.twig', ['critiques' => $critiques]);
        }

        return $this->render('erp/dashboard/fournisseur.html.twig', ['critiques' => $critiques]);
    }
}
