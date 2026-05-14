<?php

namespace App\Service;

use App\Entity\Analyse;
use App\Repository\AnalyseRepository;
use App\Repository\ConseilRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class ReportService
{
    public function __construct(
        private Environment       $twig,
        private AnalyseRepository $analyseRepo,
        private ConseilRepository $conseilRepo,
        private GroqService       $groqService
    ) {}

    // ─── Single Analyse PDF ───────────────────────────────────────────

    public function generateAnalysePdf(Analyse $analyse): string
    {
        $html = $this->twig->render('admin/report/analyse_pdf.html.twig', [
            'analyse'  => $analyse,
            'conseils' => $analyse->getConseils()->toArray(),
            'date'     => new \DateTime(),
        ]);

        return $this->renderPdf($html);
    }

    // ─── Farm Summary PDF ─────────────────────────────────────────────

    public function generateFarmReport(int $fermeId, string $farmName): string
    {
        $analyses = $this->analyseRepo->findByFermeId($fermeId);
        $total    = $this->conseilRepo->countAll();

        $priorityStats = [
            'HAUTE'   => 0,
            'MOYENNE' => 0,
            'BASSE'   => 0,
        ];

        foreach ($analyses as $analyse) {
            foreach ($analyse->getConseils() as $conseil) {
                $p = $conseil->getPrioriteRaw();
                if (isset($priorityStats[$p])) {
                    $priorityStats[$p]++;
                }
            }
        }

        $summary = $this->groqService->generateExecutiveSummary(
            $farmName,
            count($analyses),
            $total,
            $priorityStats
        );

        $html = $this->twig->render('admin/report/farm_report_pdf.html.twig', [
            'farmName'      => $farmName,
            'analyses'      => $analyses,
            'priorityStats' => $priorityStats,
            'summary'       => $summary,
            'date'          => new \DateTime(),
        ]);

        return $this->renderPdf($html);
    }

    // ─── PDF Renderer ─────────────────────────────────────────────────

    private function renderPdf(string $html): string
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
