<?php

namespace App\Controller\Admin;

use App\Entity\Analyse;
use App\Form\AnalyseAdminType;
use App\Repository\AnalyseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/analyses')]
#[IsGranted('ROLE_ADMIN')]
class AnalyseController extends AbstractController
{
    #[Route('/', name: 'admin_analyses_index', methods: ['GET'])]
    public function index(AnalyseRepository $analyseRepository): Response
    {
        return $this->render('admin/analyse/index.html.twig', [
            'analyses' => $analyseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_analyses_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $analyse = new Analyse();
        $form = $this->createForm(AnalyseAdminType::class, $analyse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($analyse);
            $em->flush();

            $this->addFlash('success', 'Analyse créée avec succès.');
            return $this->redirectToRoute('admin_analyses_index');
        }

        return $this->render('admin/analyse/new.html.twig', [
            'analyse' => $analyse,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_analyses_show', methods: ['GET'])]
    public function show(Analyse $analyse): Response
    {
        return $this->render('admin/analyse/show.html.twig', [
            'analyse' => $analyse,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_analyses_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Analyse $analyse, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AnalyseAdminType::class, $analyse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Analyse modifiée avec succès.');
            return $this->redirectToRoute('admin_analyses_index');
        }

        return $this->render('admin/analyse/edit.html.twig', [
            'analyse' => $analyse,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_analyses_delete', methods: ['POST'])]
    public function delete(Request $request, Analyse $analyse, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $analyse->getId(), $request->request->get('_token'))) {
            $em->remove($analyse);
            $em->flush();
            $this->addFlash('success', 'Analyse supprimée avec succès.');
        }

        return $this->redirectToRoute('admin_analyses_index');
    }

    #[Route('/{id}/status', name: 'admin_analyses_status', methods: ['POST'])]
    public function updateStatus(Request $request, Analyse $analyse, EntityManagerInterface $em): Response
    {
        $newStatus = $request->request->get('status');
        $validStatuses = ['en_attente', 'en_cours', 'terminee', 'annulee'];
        
        if (!in_array($newStatus, $validStatuses)) {
            $this->addFlash('error', 'Statut invalide.');
            return $this->redirectToRoute('admin_analyses_show', ['id' => $analyse->getId()]);
        }

        $analyse->setStatut($newStatus);
        $em->flush();

        $this->addFlash('success', 'Statut mis à jour avec succès.');
        return $this->redirectToRoute('admin_analyses_show', ['id' => $analyse->getId()]);
    }

    #[Route('/statistics/overview', name: 'admin_analyses_statistics', methods: ['GET'])]
    public function statistics(AnalyseRepository $analyseRepository): Response
    {
        $stats = [
            'total' => $analyseRepository->countAll(),
            'pending' => $analyseRepository->countPendingRequests(),
            'recent' => $analyseRepository->findRecent(10),
            'perFarm' => $analyseRepository->getAnalysisPerFarmStats(),
        ];

        return $this->render('admin/analyse/statistics.html.twig', [
            'stats' => $stats,
        ]);
    }
}
