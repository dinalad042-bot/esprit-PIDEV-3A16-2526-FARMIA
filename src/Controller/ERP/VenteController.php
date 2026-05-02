<?php

namespace App\Controller\ERP;

use App\Entity\ERP\Vente;
use App\Form\ERP\VenteType;
use App\Repository\ERP\VenteRepository;
use App\Service\ERP\ExchangeRateService;
use App\Service\ERP\VenteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/erp/vente', name: 'erp_vente_')]
#[IsGranted('ROLE_FOURNISSEUR')]
class VenteController extends AbstractController
{
    public function __construct(
        private VenteService $venteService,
        private ExchangeRateService $exchangeRateService
    ) {}

    #[Route('', name: 'index')]
    public function index(VenteRepository $repo): Response
    {
        return $this->render('erp/vente/index.html.twig', [
            'ventes' => $repo->findAllOrdered(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $vente = new Vente();
        $form = $this->createForm(VenteType::class, $vente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->venteService->createVente($vente);
                $this->addFlash('success', 'Vente enregistrée. Matières consommées selon la recette.');
                return $this->redirectToRoute('erp_vente_index');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('erp_vente_new');
            }
        }

        return $this->render('erp/vente/new.html.twig', [
            'form' => $form,
            'currencies' => $this->exchangeRateService->getCurrencyCodes('EUR'),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_vente_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('erp_vente_index');
        }
        try {
            $this->venteService->deleteVente($id);
            $this->addFlash('success', 'Vente supprimée. Matières restituées.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('erp_vente_index');
    }

    #[Route('/convert', name: 'convert', methods: ['POST'])]
    public function convert(Request $request): JsonResponse
    {
        $total = (float) $request->request->get('total', 0);
        $currency = strtoupper((string) $request->request->get('currency', 'TND'));
        $converted = $this->exchangeRateService->convert($total, 'EUR', $currency);
        $rate = $this->exchangeRateService->getRate('EUR', $currency);
        if ($converted === null) {
            return new JsonResponse(['error' => 'API indisponible.'], 503);
        }
        return new JsonResponse(['converted' => $converted, 'currency' => $currency, 'rate' => $rate]);
    }
}
