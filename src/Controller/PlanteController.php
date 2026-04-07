<?php

namespace App\Controller;

use App\Entity\Plante;
use App\Repository\PlanteRepository;
use App\Repository\FermeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/plante')]
class PlanteController extends AbstractController
{
    #[Route('/', name: 'app_plante_index', methods: ['GET'])]
    public function index(Request $request, PlanteRepository $pRepo, FermeRepository $fRepo): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'nom_espece');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('plante/index.html.twig', [
            'plantes' => $pRepo->findBySearchAndSort($search, $sort, $direction),
            'fermes' => $fRepo->findAll(),
            'plante_edit' => null,
            'errors' => [],
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    #[Route('/new', name: 'app_plante_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, PlanteRepository $pRepo, FermeRepository $fRepo): Response
    {
        $plante = new Plante();
        $this->mapData($plante, $request, $fRepo); // fRepo ajouté ici
        
        $violations = $validator->validate($plante);

        if (count($violations) > 0) {
            return $this->renderErrors($violations, $pRepo, $fRepo, null, $request);
        }

        $em->persist($plante);
        $em->flush();
        $this->addFlash('success', 'Plante ajoutée !');
        return $this->redirectToRoute('app_plante_index');
    }

    #[Route('/{id_plante}/edit', name: 'app_plante_edit', methods: ['GET'])]
    public function edit(Plante $plante, Request $request, PlanteRepository $pRepo, FermeRepository $fRepo): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'nom_espece');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('plante/index.html.twig', [
            'plantes' => $pRepo->findBySearchAndSort($search, $sort, $direction),
            'fermes' => $fRepo->findAll(),
            'plante_edit' => $plante,
            'errors' => [],
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    #[Route('/{id_plante}/update', name: 'app_plante_update', methods: ['POST'])]
    public function update(Request $request, Plante $plante, EntityManagerInterface $em, ValidatorInterface $validator, PlanteRepository $pRepo, FermeRepository $fRepo): Response
    {
        $this->mapData($plante, $request, $fRepo); // fRepo ajouté ici
        $violations = $validator->validate($plante);

        if (count($violations) > 0) {
            return $this->renderErrors($violations, $pRepo, $fRepo, $plante, $request);
        }

        $em->flush();
        $this->addFlash('success', 'Mise à jour réussie !');
        return $this->redirectToRoute('app_plante_index');
    }

    #[Route('/delete/{id_plante}', name: 'app_plante_delete', methods: ['POST'])]
    public function delete(Request $request, Plante $plante, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$plante->getIdPlante(), $request->request->get('_token'))) {
            $em->remove($plante);
            $em->flush();
            $this->addFlash('danger', 'Plante supprimée.');
        }
        return $this->redirectToRoute('app_plante_index');
    }

    /**
     * Mapping corrigé pour utiliser l'objet Ferme
     */
    private function mapData(Plante $plante, Request $request, FermeRepository $fRepo): void
    {
        $plante->setNomEspece($request->request->get('nom_espece') ?: null);
        $plante->setCycleVie($request->request->get('cycle_vie') ?: null);
        $plante->setQuantite($request->request->get('quantite') !== "" ? (int)$request->request->get('quantite') : null);
        
        // CORRECTION : On cherche l'objet Ferme au lieu de passer l'ID directement
        $idFerme = $request->request->get('id_ferme');
        if ($idFerme) {
            $ferme = $fRepo->find($idFerme);
            $plante->setFerme($ferme); // On appelle setFerme()
        } else {
            $plante->setFerme(null);
        }
    }

    private function renderErrors($violations, $pRepo, $fRepo, $plante_edit, Request $request): Response
    {
        $errors = [];
        foreach ($violations as $v) { $errors[$v->getPropertyPath()] = $v->getMessage(); }
        
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'nom_espece');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('plante/index.html.twig', [
            'plantes' => $pRepo->findBySearchAndSort($search, $sort, $direction),
            'fermes' => $fRepo->findAll(),
            'plante_edit' => $plante_edit,
            'errors' => $errors,
            'searchTerm' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    #[Route('/pdf', name: 'app_plante_pdf', methods: ['GET'])]
    public function generatePdf(PlanteRepository $pRepo): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('plante/pdf.html.twig', ['plantes' => $pRepo->findAll()]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="liste_plantes.pdf"',
        ]);
    }
}