<?php

namespace App\Controller;

use App\Entity\Ferme;
use App\Form\FermeType;
use App\Repository\FermeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/ferme')]
class FermeController extends AbstractController
{
    /**
     * Affiche le formulaire de création d'une nouvelle ferme
     */
    #[Route('/new', name: 'app_ferme_new', methods: ['GET'])]
    public function new(): Response
    {
        return $this->render('ferme/new.html.twig');
    }

    /**
     * PAGE PRINCIPALE : Affiche la liste ET gère l'ajout (POST)
     */
    #[Route('/', name: 'app_ferme_index', methods: ['GET', 'POST'])]
    public function index(FermeRepository $fermeRepository, Request $request, EntityManagerInterface $em): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'idFerme');
        $direction = $request->query->get('direction', 'ASC');

        $ferme = new Ferme();
        $form = $this->createForm(FermeType::class, $ferme);
        $form->handleRequest($request);

        // --- PARTIE AJOUT (POST) ---
        if ($form->isSubmitted() && $form->isValid()) {
            // Lie l'utilisateur connecté
            if ($this->getUser()) {
                $ferme->setUser($this->getUser());
            }

            $em->persist($ferme);
            $em->flush();

            $this->addFlash('success', 'Ferme ajoutée avec succès !');
            return $this->redirectToRoute('app_ferme_index');
        }

        // --- PARTIE AFFICHAGE (GET) ---
        // Utilisation de findBySearchAndSort si elle existe dans ton repo, sinon findAll()
        $fermes = method_exists($fermeRepository, 'findBySearchAndSort') 
            ? $fermeRepository->findBySearchAndSort($search, $sort, $direction)
            : $fermeRepository->findAll();

        return $this->render('ferme/index.html.twig', [
            'fermes' => $fermes,
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction,
            'form' => $form->createView(),
            'ferme_edit' => null // Mode ajout par défaut
        ]);
    }

    /**
     * Génération du catalogue PDF
     */
    #[Route('/pdf', name: 'app_ferme_pdf', methods: ['GET'])]
    public function generatePdf(FermeRepository $repo): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        
        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('ferme/pdf.html.twig', [
            'fermes' => $repo->findAll()
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="liste_fermes.pdf"'
        ]);
    }

    /**
     * Mode édition : recharge la page index avec les données de la ferme choisie
     */
    #[Route('/{id_ferme}/edit', name: 'app_ferme_edit', methods: ['GET'])]
    public function edit(Ferme $ferme, Request $request, FermeRepository $repo): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'idFerme');
        $direction = $request->query->get('direction', 'ASC');

        $form = $this->createForm(FermeType::class, $ferme);

        return $this->render('ferme/index.html.twig', [
            'fermes' => $repo->findAll(), // Ou findBySearchAndSort
            'ferme_edit' => $ferme,
            'form' => $form->createView(),
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    /**
     * Mise à jour effective en base
     */
    #[Route('/{id_ferme}/update', name: 'app_ferme_update', methods: ['POST'])]
    public function update(Request $request, Ferme $ferme, EntityManagerInterface $em, FermeRepository $repo): Response
    {
        $form = $this->createForm(FermeType::class, $ferme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'La ferme a été mise à jour.');
            return $this->redirectToRoute('app_ferme_index');
        }

        // En cas d'erreurs, afficher le formulaire avec les erreurs
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'idFerme');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('ferme/index.html.twig', [
            'fermes' => $repo->findAll(),
            'ferme_edit' => $ferme,
            'form' => $form->createView(),
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    /**
     * Suppression d'une ferme
     */
    #[Route('/delete/{id_ferme}', name: 'app_ferme_delete', methods: ['POST'])]
    public function delete(Request $request, Ferme $ferme, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ferme->getIdFerme(), $request->request->get('_token'))) {
            $em->remove($ferme);
            $em->flush();
            $this->addFlash('success', 'Ferme supprimée.');
        }
        
        return $this->redirectToRoute('app_ferme_index');
    }
}
