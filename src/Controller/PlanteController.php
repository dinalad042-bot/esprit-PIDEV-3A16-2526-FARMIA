<?php

namespace App\Controller;

use App\Entity\Plante;
use App\Form\PlanteType;
use App\Repository\PlanteRepository;
use App\Repository\FermeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/plante')]
class PlanteController extends AbstractController
{
    #[Route('/', name: 'app_plante_index', methods: ['GET', 'POST'])]
    public function index(Request $request, PlanteRepository $pRepo, FermeRepository $fRepo, EntityManagerInterface $em): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'nom_espece');
        $direction = $request->query->get('direction', 'ASC');

        $plante = new Plante();
        $form = $this->createForm(PlanteType::class, $plante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($plante);
            $em->flush();
            $this->addFlash('success', 'Plante ajoutée !');
            return $this->redirectToRoute('app_plante_index');
        }

        return $this->render('plante/index.html.twig', [
            'plantes' => $pRepo->findBySearchAndSort($search, $sort, $direction),
            'fermes' => $fRepo->findAll(),
            'plante_edit' => null,
            'form' => $form->createView(),
            'search' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    #[Route('/new', name: 'app_plante_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, PlanteRepository $pRepo, FermeRepository $fRepo): Response
    {
        $plante = new Plante();
        $form = $this->createForm(PlanteType::class, $plante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($plante);
            $em->flush();
            $this->addFlash('success', 'Plante ajoutée !');
            return $this->redirectToRoute('app_plante_index');
        }

        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'nom_espece');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('plante/index.html.twig', [
            'plantes' => $pRepo->findBySearchAndSort($search, $sort, $direction),
            'fermes' => $fRepo->findAll(),
            'plante_edit' => null,
            'form' => $form->createView(),
            'search' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    #[Route('/{id_plante}/edit', name: 'app_plante_edit', methods: ['GET'])]
    public function edit(Plante $plante, Request $request, PlanteRepository $pRepo, FermeRepository $fRepo): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'nom_espece');
        $direction = $request->query->get('direction', 'ASC');

        $form = $this->createForm(PlanteType::class, $plante);

        return $this->render('plante/index.html.twig', [
            'plantes' => $pRepo->findBySearchAndSort($search, $sort, $direction),
            'fermes' => $fRepo->findAll(),
            'plante_edit' => $plante,
            'form' => $form->createView(),
            'search' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
    }

    #[Route('/{id_plante}/update', name: 'app_plante_update', methods: ['POST'])]
    public function update(Request $request, Plante $plante, EntityManagerInterface $em, PlanteRepository $pRepo, FermeRepository $fRepo): Response
    {
        $form = $this->createForm(PlanteType::class, $plante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Mise à jour réussie !');
            return $this->redirectToRoute('app_plante_index');
        }

        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'nom_espece');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('plante/index.html.twig', [
            'plantes' => $pRepo->findBySearchAndSort($search, $sort, $direction),
            'fermes' => $fRepo->findAll(),
            'plante_edit' => $plante,
            'form' => $form->createView(),
            'search' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction
        ]);
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
