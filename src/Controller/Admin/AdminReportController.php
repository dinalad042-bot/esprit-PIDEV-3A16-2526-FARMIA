<?php

namespace App\Controller\Admin;

use App\Entity\Analyse;
use App\Service\ReportService;
use App\Service\WeatherService;
use App\Repository\AnalyseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/report', name: 'admin_report_')]
class AdminReportController extends AbstractController
{
    public function __construct(
        private ReportService    $reportService,
        private WeatherService   $weatherService,
        private AnalyseRepository $analyseRepo
    ) {}

    // ─── PDF: Single Analyse ──────────────────────────────────────────

    #[Route('/analyse/{id}/pdf', name: 'analyse_pdf', methods: ['GET'])]
    public function analysePdf(Analyse $analyse): Response
    {
        $pdf = $this->reportService->generateAnalysePdf($analyse);

        return new Response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="analyse-' . $analyse->getId() . '.pdf"',
        ]);
    }

    // ─── Weather for Ferme location ───────────────────────────────────

    #[Route('/analyse/{id}/weather', name: 'weather', methods: ['GET'])]
    public function weather(Analyse $analyse): Response
    {
        $location = $analyse->getFerme()?->getLieu() ?? 'Tunis';
        $weather  = $this->weatherService->getWeatherForLocation($location);
        $advice   = $this->weatherService->getAgriAdvice($weather);

        return $this->render('admin/report/weather_widget.html.twig', [
            'analyse'  => $analyse,
            'weather'  => $weather,
            'advice'   => $advice,
        ]);
    }

    // ─── Dashboard Stats API ──────────────────────────────────────────

    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(): Response
    {
        $farmStats     = $this->analyseRepo->getAnalysisPerFarmStats();
        $recentAnalyses = $this->analyseRepo->findRecent(10);

        return $this->json([
            'farmStats'     => $farmStats,
            'recentCount'   => count($recentAnalyses),
        ]);
    }
}
