<?php

namespace App\Controller;

use App\Entity\Conseil;
use App\Form\ConseilType;
use App\Repository\ConseilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/conseil', name: 'app_conseil_')]
class ConseilController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ConseilRepository $repo
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search   = $request->query->get('search', '');
        $priorite = $request->query->get('priorite', '');

        $qb = $this->em->getRepository(\App\Entity\Conseil::class)
            ->createQueryBuilder('c');

        if ($search !== '') {
            $qb->andWhere('c.descriptionConseil LIKE :term')
               ->setParameter('term', '%' . $search . '%');
        }

        if ($priorite !== '') {
            $qb->andWhere('c.prioriteRaw = :p')
               ->setParameter('p', $priorite);
        }

        $conseils = $qb->orderBy('c.id', 'DESC')
                       ->getQuery()
                       ->getResult();

        return $this->render('conseil/index.html.twig', [
            'conseils' => $conseils,
            'search'   => $search,
            'priorite' => $priorite,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $conseil = new Conseil();
        $form    = $this->createForm(ConseilType::class, $conseil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($conseil);
            $this->em->flush();
            $this->addFlash('success', 'Conseil créé avec succès.');
            return $this->redirectToRoute('app_conseil_index');
        }

        return $this->render('conseil/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Conseil $conseil): Response
    {
        return $this->render('conseil/show.html.twig', [
            'conseil' => $conseil,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Conseil $conseil): Response
    {
        $form = $this->createForm(ConseilType::class, $conseil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Conseil modifié avec succès.');
            return $this->redirectToRoute('app_conseil_index');
        }

        return $this->render('conseil/edit.html.twig', [
            'form'    => $form->createView(),
            'conseil' => $conseil,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Conseil $conseil): Response
    {
        if ($this->isCsrfTokenValid('delete'.$conseil->getId(), $request->request->get('_token'))) {
            $this->em->remove($conseil);
            $this->em->flush();
            $this->addFlash('success', 'Conseil supprimé.');
        }
        return $this->redirectToRoute('app_conseil_index');
    }
}