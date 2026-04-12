<?php

namespace App\Controller\Web;

use App\Repository\AnalyseRepository;
use App\Repository\ConseilRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    public function __construct(
        private AnalyseRepository $analyseRepo,
        private ConseilRepository $conseilRepo
    ) {}

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
        $user = $this->getUser();
        $userId = $user->getId();

        $stats = [
            'analysesThisMonth' => $this->analyseRepo->countByTechnicienThisMonth($userId),
            'analysesTotal' => $this->analyseRepo->countByTechnicien($userId),
            'conseilsTotal' => $this->conseilRepo->countByTechnicien($userId),
            'conseilsUrgent' => $this->conseilRepo->countByTechnicienAndPriorite($userId, 'HAUTE'),
            'pendingRequests' => $this->analyseRepo->countPendingRequests(),
        ];

        return $this->render('portal/expert/index.html.twig', [
            'user' => $user,
            'stats' => $stats
        ]);
    }

    #[Route('/agricole/dashboard', name: 'dashboard_agricole')]
    #[IsGranted('ROLE_AGRICOLE')]
    public function agricole(): Response
    {
        $user = $this->getUser();
        $fermes = $user->getFermes();
        
        // Calculate real stats
        $fermeCount = $fermes->count();
        $planteCount = 0;
        $animalCount = 0;
        $conseilCount = 0;
        
        foreach ($fermes as $ferme) {
            $planteCount += $ferme->getPlantes()->count();
            $animalCount += $ferme->getAnimals()->count();
            // Count conseils from analyses of this ferme
            foreach ($ferme->getAnalyses() as $analyse) {
                $conseilCount += $analyse->getConseils()->count();
            }
        }
        
        return $this->render('portal/agricole/index.html.twig', [
            'user' => $user,
            'fermeCount' => $fermeCount,
            'planteCount' => $planteCount,
            'animalCount' => $animalCount,
            'conseilCount' => $conseilCount,
            'hasFermes' => $fermeCount > 0
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
