<?php

namespace App\Controller\ERP;

use App\Entity\ERP\ServiceERP;
use App\Form\ERP\ServiceERPType;
use App\Repository\ERP\ServiceERPRepository;
use App\Service\ERP\StockService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/erp/service', name: 'erp_service_')]
#[IsGranted('ROLE_FOURNISSEUR')]
class ServiceERPController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'index')]
    public function index(ServiceERPRepository $repo, StockService $stockService): Response
    {
        return $this->render('erp/service/index.html.twig', [
            'services' => $repo->findAllOrderedById(),
            'critiques' => $stockService->findStockCritique(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $service = new ServiceERP();
        $form = $this->createForm(ServiceERPType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($service);
            $this->em->flush();
            $this->addFlash('success', 'Service créé avec succès.');
            return $this->redirectToRoute('erp_service_index');
        }

        return $this->render('erp/service/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, ServiceERPRepository $repo): Response
    {
        $service = $repo->find($id);
        if (!$service) {
            $this->addFlash('error', 'Service introuvable.');
            return $this->redirectToRoute('erp_service_index');
        }

        $form = $this->createForm(ServiceERPType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Service mis à jour.');
            return $this->redirectToRoute('erp_service_index');
        }

        return $this->render('erp/service/edit.html.twig', ['form' => $form, 'service' => $service]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(int $id, Request $request, ServiceERPRepository $repo): Response
    {
        $service = $repo->find($id);
        if (!$service) {
            $this->addFlash('error', 'Service introuvable.');
            return $this->redirectToRoute('erp_service_index');
        }

        if (!$this->isCsrfTokenValid('delete_service_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('erp_service_index');
        }

        try {
            $this->em->remove($service);
            $this->em->flush();
            $this->addFlash('success', 'Service supprimé.');
        } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Impossible de supprimer : ce service est référencé par des achats ou ventes.');
        }

        return $this->redirectToRoute('erp_service_index');
    }
}
