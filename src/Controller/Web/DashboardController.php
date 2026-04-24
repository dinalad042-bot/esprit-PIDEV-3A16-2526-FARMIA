<?php

namespace App\Controller\Web;

// Ajout des Repositories avec les bons noms : Plante et Animal
use App\Repository\FermeRepository;
use App\Repository\PlanteRepository; 
use App\Repository\AnimalRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
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
        return $this->render('portal/expert/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    // -----------------------------------------------------------
    // --- ESPACE AGRICOLE ---
    // -----------------------------------------------------------

    #[Route('/agricole/dashboard', name: 'dashboard_agricole')]
    #[IsGranted('ROLE_AGRICOLE')]
    public function agricole(
        FermeRepository $fermeRepo, 
        PlanteRepository $planteRepo, // Changé ici (Plante au lieu de Culture)
        AnimalRepository $animalRepo
    ): Response {
        // Comptage dynamique depuis la base de données
        $nbFermes = $fermeRepo->count([]);
        $nbPlantes = $planteRepo->count([]); // Changé ici
        $nbAnimaux = $animalRepo->count([]);

        return $this->render('portal/agricole/index.html.twig', [
            'user' => $this->getUser(),
            // Transmission des variables à la vue Twig
            'nb_fermes' => $nbFermes,
            'nb_plantes' => $nbPlantes, // Changé ici
            'nb_animaux' => $nbAnimaux,
        ]);
    }

    // Ajout de la route pour le sous-menu "Gestion de l'Exploitation"
    #[Route('/agricole/exploitation', name: 'app_exploitation')]
    #[IsGranted('ROLE_AGRICOLE')]
    public function exploitation(): Response
    {
        // Assure-toi de placer ton fichier twig d'exploitation dans ce dossier
        return $this->render('portal/agricole/exploitation.html.twig', [
            'user' => $this->getUser()
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