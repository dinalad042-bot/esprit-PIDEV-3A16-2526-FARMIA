<?php

namespace App\Controller;

use App\Entity\Ferme;
use App\Repository\FermeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/ferme')]
class FermeController extends AbstractController
{
    /**
     * Affichage de la liste avec recherche et tri
     */
    #[Route('/', name: 'app_ferme_index', methods: ['GET'])]
    public function index(Request $request, FermeRepository $repo): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'nom_ferme'); 
        $direction = $request->query->get('direction', 'ASC');

        $fermes = $repo->findBySearchAndSort($search, $sort, $direction);

        return $this->render('ferme/index.html.twig', [
            'fermes' => $fermes,
            'ferme_edit' => null,
            'errors' => [],
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    /**
     * Création d'une nouvelle ferme
     */
    #[Route('/new', name: 'app_ferme_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, FermeRepository $repo): Response
    {
        $ferme = new Ferme();
        $this->mapData($ferme, $request);
        
        $violations = $validator->validate($ferme);

        if (count($violations) > 0) {
            return $this->renderWithErrors($violations, $repo, null, $request);
        }

        $em->persist($ferme);
        $em->flush();

        $this->addFlash('success', 'La ferme a été ajoutée avec succès.');
        return $this->redirectToRoute('app_ferme_index');
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
     * Chargement d'une ferme dans le formulaire de modification
     */
    #[Route('/{id_ferme}/edit', name: 'app_ferme_edit', methods: ['GET'])]
    public function edit(Ferme $ferme, Request $request, FermeRepository $repo): Response
    {
        // On récupère les filtres actuels pour ne pas les perdre
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'nom_ferme');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('ferme/index.html.twig', [
            'fermes' => $repo->findBySearchAndSort($search, $sort, $direction),
            'ferme_edit' => $ferme,
            'errors' => [],
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    /**
     * Mise à jour des données en base
     */
    #[Route('/{id_ferme}/update', name: 'app_ferme_update', methods: ['POST'])]
    public function update(Request $request, Ferme $ferme, EntityManagerInterface $em, ValidatorInterface $validator, FermeRepository $repo): Response
    {
        $this->mapData($ferme, $request);
        
        $violations = $validator->validate($ferme);
        if (count($violations) > 0) {
            return $this->renderWithErrors($violations, $repo, $ferme, $request);
        }

        $em->flush();
        $this->addFlash('success', 'La ferme a été mise à jour.');
        
        return $this->redirectToRoute('app_ferme_index');
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

    /**
     * Mapping manuel des données du formulaire vers l'entité
     */
    private function mapData(Ferme $ferme, Request $request): void
    {
        $ferme->setNomFerme($request->request->get('nom_ferme'));
        $ferme->setLieu($request->request->get('lieu'));
        // Conversion sécurisée en float pour la surface
        $surface = $request->request->get('surface');
        $ferme->setSurface($surface !== null ? (float)$surface : 0.0);
    }

    /**
     * Centralisation de l'affichage en cas d'erreurs de validation
     */
    private function renderWithErrors($violations, FermeRepository $repo, ?Ferme $ferme_edit, Request $request): Response
    {
        $errors = [];
        foreach ($violations as $v) { 
            $errors[$v->getPropertyPath()] = $v->getMessage(); 
        }

        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'nom_ferme');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('ferme/index.html.twig', [
            'fermes' => $repo->findBySearchAndSort($search, $sort, $direction),
            'ferme_edit' => $ferme_edit,
            'errors' => $errors,
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }
}