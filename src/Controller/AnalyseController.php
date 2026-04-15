<?php

namespace App\Controller;

use App\Entity\Analyse;
use App\Form\AnalyseType;
use App\Repository\AnalyseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/analyse', name: 'app_analyse_')]
class AnalyseController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private AnalyseRepository $repo
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search   = $request->query->get('search', '');
        $analyses = $search
            ? $this->repo->search($search)
            : $this->repo->findBy([], ['dateAnalyse' => 'DESC']);

        return $this->render('analyse/index.html.twig', [
            'analyses' => $analyses,
            'search'   => $search,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $analyse = new Analyse();
        $form    = $this->createForm(AnalyseType::class, $analyse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($analyse);
            $this->em->flush();
            $this->addFlash('success', 'Analyse créée avec succès.');
            return $this->redirectToRoute('app_analyse_index');
        }

        return $this->render('analyse/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Analyse $analyse): Response
    {
        return $this->render('analyse/show.html.twig', [
            'analyse' => $analyse,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Analyse $analyse): Response
    {
        $form = $this->createForm(AnalyseType::class, $analyse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Analyse modifiée avec succès.');
            return $this->redirectToRoute('app_analyse_index');
        }

        return $this->render('analyse/edit.html.twig', [
            'form'    => $form->createView(),
            'analyse' => $analyse,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Analyse $analyse): Response
    {
        if ($this->isCsrfTokenValid('delete'.$analyse->getId(), $request->request->get('_token'))) {
            $this->em->remove($analyse);
            $this->em->flush();
            $this->addFlash('success', 'Analyse supprimée.');
        }
        return $this->redirectToRoute('app_analyse_index');
    }
}