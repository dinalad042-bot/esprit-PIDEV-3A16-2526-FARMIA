<?php

namespace App\Controller\Web;

use App\Entity\Conseil;
use App\Enum\Priorite;
use App\Form\ConseilType;
use App\Repository\ConseilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/expert')]
#[IsGranted('ROLE_EXPERT')]
class ExpertConseilController extends AbstractController
{
    public function __construct(
        private ConseilRepository $conseilRepo,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/conseils', name: 'expert_conseils_list')]
    public function list(Request $request): Response
    {
        $user = $this->getUser();
        $search = $request->query->get('search', '');
        $priorite = $request->query->get('priorite', '');

        // Fetch conseils related to analyses where the expert is the technicien
        $conseils = $this->conseilRepo->findByExpert($user->getId(), $search, $priorite);

        return $this->render('portal/expert/conseils.html.twig', [
            'conseils' => $conseils,
            'search' => $search,
            'priorite' => $priorite,
            'priorites' => Priorite::cases(),
        ]);
    }

    #[Route('/conseil/{id}', name: 'expert_conseil_show', requirements: ['id' => '\d+'])]
    public function show(Conseil $conseil): Response
    {
        // Security check: ensure the expert is the technicien for the related analysis
        if ($conseil->getAnalyse()->getTechnicien() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir ce conseil.');
        }

        return $this->render('portal/expert/conseil_show.html.twig', [
            'conseil' => $conseil,
        ]);
    }

    #[Route('/conseil/new', name: 'expert_conseil_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $conseil = new Conseil();
        $form = $this->createForm(ConseilType::class, $conseil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($conseil);
            $this->em->flush();
            $this->addFlash('success', 'Conseil créé avec succès.');
            return $this->redirectToRoute('expert_conseils_list');
        }

        return $this->render('portal/expert/conseil_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/conseil/{id}/edit', name: 'expert_conseil_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Conseil $conseil): Response
    {
        // Security check: ensure the expert is the technicien for the related analysis
        if ($conseil->getAnalyse()->getTechnicien() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce conseil.');
        }

        $form = $this->createForm(ConseilType::class, $conseil, [
            'analyse_id' => $conseil->getAnalyse()->getId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Conseil modifié avec succès.');
            return $this->redirectToRoute('expert_conseil_show', ['id' => $conseil->getId()]);
        }

        return $this->render('portal/expert/conseil_edit.html.twig', [
            'form' => $form->createView(),
            'conseil' => $conseil,
        ]);
    }

    #[Route('/conseil/{id}/delete', name: 'expert_conseil_delete', methods: ['POST'])]
    public function delete(Request $request, Conseil $conseil): Response
    {
        // Security check: ensure the expert is the technicien for the related analysis
        if ($conseil->getAnalyse()->getTechnicien() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce conseil.');
        }

        if ($this->isCsrfTokenValid('delete'.$conseil->getId(), $request->request->get('_token'))) {
            $analyseId = $conseil->getAnalyse()->getId();
            $this->em->remove($conseil);
            $this->em->flush();
            $this->addFlash('success', 'Conseil supprimé avec succès.');
            
            // Redirect back to analyse show if coming from there
            $referer = $request->headers->get('referer');
            if ($referer && str_contains($referer, '/expert/analyse/')) {
                return $this->redirectToRoute('expert_analyse_show', ['id' => $analyseId]);
            }
        }

        return $this->redirectToRoute('expert_conseils_list');
    }
}