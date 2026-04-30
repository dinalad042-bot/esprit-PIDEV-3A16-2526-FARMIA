<?php

namespace App\Controller\ERP;

use App\Entity\ERP\Matiere;
use App\Form\ERP\MatiereType;
use App\Repository\ERP\MatiereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/erp/matiere', name: 'erp_matiere_')]
#[IsGranted('ROLE_AGRICOLE')]
class MatiereController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'index')]
    public function index(MatiereRepository $repo): Response
    {
        return $this->render('erp/matiere/index.html.twig', [
            'matieres' => $repo->findAllOrdered(),
            'critiques' => $repo->findStockCritique(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $matiere = new Matiere();
        $form = $this->createForm(MatiereType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($matiere);
            $this->em->flush();
            $this->addFlash('success', 'Matière créée.');
            return $this->redirectToRoute('erp_matiere_index');
        }

        return $this->render('erp/matiere/form.html.twig', ['form' => $form, 'title' => 'Nouvelle matière']);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, MatiereRepository $repo): Response
    {
        $matiere = $repo->find($id);
        if (!$matiere) {
            $this->addFlash('error', 'Matière introuvable.');
            return $this->redirectToRoute('erp_matiere_index');
        }

        $form = $this->createForm(MatiereType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Matière mise à jour.');
            return $this->redirectToRoute('erp_matiere_index');
        }

        return $this->render('erp/matiere/form.html.twig', ['form' => $form, 'title' => 'Modifier ' . $matiere->getNom()]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(int $id, Request $request, MatiereRepository $repo): Response
    {
        $matiere = $repo->find($id);
        if (!$matiere || !$this->isCsrfTokenValid('del_matiere_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Action invalide.');
            return $this->redirectToRoute('erp_matiere_index');
        }
        try {
            $this->em->remove($matiere);
            $this->em->flush();
            $this->addFlash('success', 'Matière supprimée.');
        } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Impossible : cette matière est utilisée dans une recette ou un achat.');
        }
        return $this->redirectToRoute('erp_matiere_index');
    }
}
