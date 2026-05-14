<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/statistics')]
#[IsGranted('ROLE_ADMIN')]
class AdminStatisticsController extends AbstractController
{
    #[Route('/data', name: 'admin_statistics_data', methods: ['GET'])]
    public function data(UserRepository $userRepository): JsonResponse
    {
        // SELECT role, COUNT(*) FROM user GROUP BY role;
        $users = $userRepository->findAll();
        $distribution = [];

        foreach ($users as $user) {
            $role = strtoupper(str_replace('ROLE_', '', $user->getRole() ?: 'USER'));
            if (!isset($distribution[$role])) {
                $distribution[$role] = 0;
            }
            $distribution[$role]++;
        }

        // Prepare data for Chart.js Pie/Donut format
        $labels = [];
        $data = [];
        $colors = [];
        
        $colorMap = [
            'ADMIN' => '#8b5cf6', // Purple
            'EXPERT' => '#3b82f6', // Blue
            'AGRICOLE' => '#4ade80', // Green
            'FOURNISSEUR' => '#f59e0b', // Orange
            'USER' => '#94a3b8' // Gray
        ];

        foreach ($distribution as $role => $count) {
            $labels[] = ucfirst(strtolower($role)) . ' (' . $count . ')';
            $data[] = $count;
            $colors[] = $colorMap[$role] ?? '#cbd5e1';
        }

        return new JsonResponse([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff'
                ]
            ]
        ]);
    }
}
