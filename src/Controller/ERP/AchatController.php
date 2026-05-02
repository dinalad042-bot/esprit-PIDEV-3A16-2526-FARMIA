<?php

namespace App\Controller\ERP;

use App\Entity\ERP\Achat;
use App\Form\ERP\AchatType;
use App\Repository\ERP\AchatRepository;
use App\Service\ERP\AchatService;
use App\Service\ERP\ExchangeRateService;
use App\Service\ERP\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/erp/achat', name: 'erp_achat_')]
#[IsGranted('ROLE_AGRICOLE')]
class AchatController extends AbstractController
{
    public function __construct(
        private AchatService $achatService,
        private ExchangeRateService $exchangeRateService,
        private PaymentService $paymentService,
        private EntityManagerInterface $em
    ) {}

    #[Route('', name: 'index')]
    public function index(AchatRepository $repo): Response
    {
        return $this->render('erp/achat/index.html.twig', [
            'achats' => $repo->findAllWithLignes(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $achat = new Achat();
        $form = $this->createForm(AchatType::class, $achat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Merge duplicate matiere lines
            $merged = [];
            foreach ($achat->getLignes() as $ligne) {
                $id = $ligne->getMatiere()->getIdMatiere();
                if (isset($merged[$id])) {
                    $merged[$id]->setQuantite($merged[$id]->getQuantite() + $ligne->getQuantite());
                } else {
                    $merged[$id] = $ligne;
                }
            }
            $achat->getLignes()->clear();
            foreach ($merged as $ligne) {
                $achat->addLigne($ligne);
            }

            try {
                $this->achatService->createAchat($achat);
                $this->addFlash('success', 'Achat enregistré. Stock matières mis à jour.');
                return $this->redirectToRoute('erp_achat_index');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('erp_achat_new');
            }
        }

        return $this->render('erp/achat/new.html.twig', [
            'form' => $form,
            'currencies' => $this->exchangeRateService->getCurrencyCodes('EUR'),
        ]);
    }

    #[Route('/{id}/show', name: 'show', methods: ['GET'])]
    public function show(int $id, AchatRepository $repo): Response
    {
        $achat = $repo->findByIdWithLignes($id);
        if (!$achat) {
            $this->addFlash('error', 'Achat introuvable.');
            return $this->redirectToRoute('erp_achat_index');
        }
        return $this->render('erp/achat/show.html.twig', ['achat' => $achat, 'qrPath' => null]);
    }

    #[Route('/{id}/pay', name: 'pay', methods: ['POST'])]
    public function pay(int $id, Request $request, AchatRepository $repo): Response
    {
        if (!$this->isCsrfTokenValid('pay_achat_' . $id, $request->request->get('_token'))) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['error' => 'Token CSRF invalide.'], 403);
            }
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('erp_achat_index');
        }
        $achat = $repo->findByIdWithLignes($id);
        if (!$achat || $achat->getTotal() <= 0) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['error' => 'Achat invalide.'], 400);
            }
            $this->addFlash('error', 'Achat invalide.');
            return $this->redirectToRoute('erp_achat_index');
        }

        $successUrl = $this->generateUrl('erp_achat_payment_success', ['id' => $id], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl  = $this->generateUrl('erp_achat_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $url = $this->paymentService->createCheckoutSession($achat->getTotal(), $successUrl, $cancelUrl);
        if (!$url) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['error' => 'Impossible de créer la session Stripe. Vérifiez votre clé API.'], 500);
            }
            $this->addFlash('error', 'Impossible de créer la session Stripe.');
            return $this->redirectToRoute('erp_achat_index');
        }

        $achat->setPaid(true);
        $this->em->flush();
        $qrPath = $this->paymentService->generateQrCode($url);

        // AJAX request from the modal
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success'   => true,
                'stripeUrl' => $url,
                'qrPath'    => $qrPath,
                'total'     => number_format($achat->getTotal(), 2, ',', ' '),
            ]);
        }

        return $this->render('erp/achat/show.html.twig', [
            'achat'     => $achat,
            'qrPath'    => $qrPath,
            'stripeUrl' => $url,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_achat_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('erp_achat_index');
        }
        try {
            $this->achatService->deleteAchat($id);
            $this->addFlash('success', 'Achat supprimé. Stock matières restauré.');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('erp_achat_index');
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

    /**
     * Stripe redirects here after successful payment.
     * Marks the achat as paid and shows a confirmation page.
     */
    #[Route('/{id}/payment-success', name: 'payment_success', methods: ['GET'])]
    public function paymentSuccess(int $id, AchatRepository $repo): Response
    {
        $achat = $repo->findByIdWithLignes($id);
        if (!$achat) {
            $this->addFlash('error', 'Achat introuvable.');
            return $this->redirectToRoute('erp_achat_index');
        }

        // Mark as paid if not already
        if (!$achat->isPaid()) {
            $achat->setPaid(true);
            $this->em->flush();
        }

        return $this->render('erp/achat/payment_success.html.twig', [
            'achat' => $achat,
        ]);
    }
}
