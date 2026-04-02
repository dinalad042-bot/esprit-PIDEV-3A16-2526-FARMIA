<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller for modern Admin Statistics.
 */
class StatisticsController extends AbstractController
{
    #[Route('/admin/statistics', name: 'admin_statistics')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository): Response
    {
        $roleDistribution = $userRepository->countUsersByRole();
        
        // Prepare data for Chart.js
        $labels = [];
        $counts = [];
        foreach ($roleDistribution as $entry) {
            $labels[] = ucfirst(strtolower($entry['role'] ?? 'Utilisateur'));
            $counts[] = $entry['count'];
        }

        return $this->render('admin/statistics/index.html.twig', [
            'labels' => $labels,
            'counts' => $counts,
        ]);
    }
}
