<?php

namespace App\Controller\Web;

use App\Repository\FermeRepository;
use App\Repository\PlanteRepository;
use App\Repository\AnimalRepository;
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
            'analysesTotal'     => $this->analyseRepo->countByTechnicien($userId),
            'conseilsTotal'     => $this->conseilRepo->countByTechnicien($userId),
            'conseilsUrgent'    => $this->conseilRepo->countByTechnicienAndPriorite($userId, 'HAUTE'),
            'pendingRequests'   => $this->analyseRepo->countPendingRequests(),
        ];

        return $this->render('portal/expert/index.html.twig', [
            'user'  => $user,
            'stats' => $stats,
        ]);
    }

    // -----------------------------------------------------------
    // --- ESPACE AGRICOLE ---
    // -----------------------------------------------------------

    #[Route('/agricole/dashboard', name: 'dashboard_agricole')]
    #[IsGranted('ROLE_AGRICOLE')]
    public function agricole(
        FermeRepository $fermeRepo,
        PlanteRepository $planteRepo,
        AnimalRepository $animalRepo
    ): Response {
        $user = $this->getUser();
        $fermes = $user->getFermes();

        $fermeCount  = $fermes->count();
        $planteCount = 0;
        $animalCount = 0;
        $conseilCount = 0;

        foreach ($fermes as $ferme) {
            $planteCount += $ferme->getPlantes()->count();
            $animalCount += $ferme->getAnimals()->count();
            foreach ($ferme->getAnalyses() as $analyse) {
                $conseilCount += $analyse->getConseils()->count();
            }
        }

        return $this->render('portal/agricole/index.html.twig', [
            'user'         => $user,
            'nb_fermes'    => $fermeCount,
            'nb_plantes'   => $planteCount,
            'nb_animaux'   => $animalCount,
            'fermeCount'   => $fermeCount,
            'planteCount'  => $planteCount,
            'animalCount'  => $animalCount,
            'conseilCount' => $conseilCount,
            'hasFermes'    => $fermeCount > 0,
        ]);
    }

    #[Route('/agricole/exploitation', name: 'app_exploitation')]
    #[IsGranted('ROLE_AGRICOLE')]
    public function exploitation(
        FermeRepository $fermeRepo,
        PlanteRepository $planteRepo,
        AnimalRepository $animalRepo
    ): Response {
        $user = $this->getUser();
        $fermes = $user->getFermes();

        $fermeCount  = $fermes->count();
        $planteCount = 0;
        $animalCount = 0;
        $conseilCount = 0;

        foreach ($fermes as $ferme) {
            $planteCount += $ferme->getPlantes()->count();
            $animalCount += $ferme->getAnimals()->count();
            foreach ($ferme->getAnalyses() as $analyse) {
                $conseilCount += $analyse->getConseils()->count();
            }
        }

        return $this->render('portal/agricole/exploitation.html.twig', [
            'user'         => $user,
            'fermeCount'   => $fermeCount,
            'planteCount'  => $planteCount,
            'animalCount'  => $animalCount,
            'conseilCount' => $conseilCount,
            'hasFermes'    => $fermeCount > 0,
        ]);
    }

    // -----------------------------------------------------------
    // --- ESPACE FOURNISSEUR ---
    // -----------------------------------------------------------

    #[Route('/fournisseur/dashboard', name: 'dashboard_fournisseur')]
    #[IsGranted('ROLE_FOURNISSEUR')]
    public function fournisseur(): Response
    {
        return $this->render('portal/fournisseur/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }
}
