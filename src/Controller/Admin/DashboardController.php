<?php

namespace App\Controller\Admin;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Modernized Admin Dashboard Controller.
 */
class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserService $userService): Response
    {
        $users = $userService->findAll();
        
        $stats = [
            'total' => count($users),
            'admins' => count(array_filter($users, fn($u) => $u->getRole() === 'ADMIN')),
            'experts' => count(array_filter($users, fn($u) => $u->getRole() === 'EXPERT')),
            'agricoles' => count(array_filter($users, fn($u) => $u->getRole() === 'AGRICOLE')),
            'fournisseurs' => count(array_filter($users, fn($u) => $u->getRole() === 'FOURNISSEUR')),
        ];

        return $this->render('admin/dashboard/index.html.twig', [
            'user' => $this->getUser(),
            'stats' => $stats
        ]);
    }
}
