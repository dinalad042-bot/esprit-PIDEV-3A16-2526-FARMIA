<?php

namespace App\Controller\Admin;

use App\Entity\Conseil;
use App\Entity\Analyse;
use App\Form\ConseilAdminType;
use App\Repository\ConseilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/conseils')]
#[IsGranted('ROLE_ADMIN')]
class ConseilController extends AbstractController
{
    #[Route('/', name: 'admin_conseils_index', methods: ['GET'])]
    public function index(ConseilRepository $conseilRepository): Response
    {
        return $this->render('admin/conseil/index.html.twig', [
            'conseils' => $conseilRepository->findAll(),
        ]);
    }

    #[Route('/new/{analyseId}', name: 'admin_conseils_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $analyseId, EntityManagerInterface $em): Response
    {
        $analyse = $em->getRepository(Analyse::class)->find($analyseId);
        if (!$analyse) {
            throw $this->createNotFoundException('Analyse non trouvée.');
        }

        $conseil = new Conseil();
        $conseil->setAnalyse($analyse);
        
        $form = $this->createForm(ConseilAdminType::class, $conseil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($conseil);
            $em->flush();

            $this->addFlash('success', 'Conseil créé avec succès.');
            return $this->redirectToRoute('admin_analyses_show', ['id' => $analyseId]);
        }

        return $this->render('admin/conseil/new.html.twig', [
            'conseil' => $conseil,
            'analyse' => $analyse,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_conseils_show', methods: ['GET'])]
    public function show(Conseil $conseil): Response
    {
        return $this->render('admin/conseil/show.html.twig', [
            'conseil' => $conseil,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_conseils_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Conseil $conseil, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ConseilAdminType::class, $conseil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Conseil modifié avec succès.');
            return $this->redirectToRoute('admin_analyses_show', ['id' => $conseil->getAnalyse()->getId()]);
        }

        return $this->render('admin/conseil/edit.html.twig', [
            'conseil' => $conseil,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_conseils_delete', methods: ['POST'])]
    public function delete(Request $request, Conseil $conseil, EntityManagerInterface $em): Response
    {
        $analyseId = $conseil->getAnalyse()->getId();
        
        if ($this->isCsrfTokenValid('delete' . $conseil->getId(), $request->request->get('_token'))) {
            $em->remove($conseil);
            $em->flush();
            $this->addFlash('success', 'Conseil supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_analyses_show', ['id' => $analyseId]);
    }

    #[Route('/statistics/overview', name: 'admin_conseils_statistics', methods: ['GET'])]
    public function statistics(ConseilRepository $conseilRepository): Response
    {
        $stats = [
            'total' => count($conseilRepository->findAll()),
            'byPriority' => $conseilRepository->countByPriority(),
            'recent' => $conseilRepository->findRecent(10),
        ];

        return $this->render('admin/conseil/statistics.html.twig', [
            'stats' => $stats,
        ]);
    }
}
