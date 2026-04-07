<?php

namespace App\Controller;

use App\Repository\AnalyseRepository;
use App\Repository\ConseilRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        private AnalyseRepository $analyseRepo,
        private ConseilRepository $conseilRepo
    ) {}

    #[Route('/expert/dashboard', name: 'dashboard_expert')]
    public function expert(): Response
    {
        $totalAnalyses = $this->analyseRepo->countAll();
        $totalConseils = $this->conseilRepo->countAll();
        $recentAnalyses = $this->analyseRepo->findRecent(5);
        $priorityStats  = $this->conseilRepo->getPriorityStats();
        $farmStats      = $this->analyseRepo->getAnalysisPerFarmStats();

        // Build priority counts for display
        $priorityCounts = ['HAUTE' => 0, 'MOYENNE' => 0, 'BASSE' => 0];
        foreach ($priorityStats as $stat) {
            $priorityCounts[$stat['priorite']] = $stat['total'];
        }

        return $this->render('dashboard/expert.html.twig', [
            'totalAnalyses'  => $totalAnalyses,
            'totalConseils'  => $totalConseils,
            'recentAnalyses' => $recentAnalyses,
            'priorityCounts' => $priorityCounts,
            'farmStats'      => $farmStats,
        ]);
    }
}