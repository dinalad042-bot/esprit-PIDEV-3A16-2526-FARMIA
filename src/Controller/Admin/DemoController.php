<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/demo')]
#[IsGranted('ROLE_ADMIN')]
class DemoController extends AbstractController
{
    #[Route('/quick-ref', name: 'admin_demo_ref')]
    public function quickRef(): Response
    {
        $links = [
            [
                'title' => '🚀 START HERE',
                'description' => 'Admin Analyse List - Show search, total stats cards, and data from Java DB.',
                'url' => '/admin/analyse',
                'badge' => 'BackOffice',
                'color' => '#2e7d32',
                'icon' => '📋'
            ],
            [
                'title' => '🧠 AI DEMO',
                'description' => 'AI Diagnostic - Show LLM symptoms analysis, confidence badge, and treatment.',
                'url' => '/admin/analyse/1/ai-diagnostic',
                'badge' => 'Séance 10 ⭐',
                'color' => '#673ab7',
                'icon' => '🤖',
                'copy' => 'Les feuilles présentent des taches jaunes avec flétrissement progressif'
            ],
            [
                'title' => '📄 PDF DEMO',
                'description' => 'Professional PDF Export using DomPDF - Professional reports for farmers.',
                'url' => '/admin/report/analyse/1/pdf',
                'badge' => 'DomPDF',
                'color' => '#d32f2f',
                'icon' => '📄'
            ],
            [
                'title' => '🌤️ WEATHER',
                'description' => 'Live Weather API - Agricultural advice based on real-time climate.',
                'url' => '/admin/report/analyse/1/weather',
                'badge' => 'OpenWeather',
                'color' => '#0288d1',
                'icon' => '🌡️'
            ],
            [
                'title' => '🌐 FO DEMO',
                'description' => 'FrontOffice View - Card-based grid for modern user experience.',
                'url' => '/analyse',
                'badge' => 'FrontOffice',
                'color' => '#f57c00',
                'icon' => '🖥️'
            ],
            [
                'title' => '📊 DASHBOARD',
                'description' => 'Expert Dashboard - Visualization of analyses and priority breakdown.',
                'url' => '/expert/dashboard',
                'badge' => 'Expert',
                'color' => '#455a64',
                'icon' => '📈'
            ],
        ];

        return $this->render('admin/demo/quick_ref.html.twig', [
            'links' => $links,
        ]);
    }
}
