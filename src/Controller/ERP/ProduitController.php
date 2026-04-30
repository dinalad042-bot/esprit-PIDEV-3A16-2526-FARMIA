<?php

namespace App\Controller\ERP;

use App\Entity\ERP\Produit;
use App\Form\ERP\ProduitType;
use App\Repository\ERP\ProduitRepository;
use App\Service\ERP\MatiereService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/erp/produit', name: 'erp_produit_')]
class ProduitController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private MatiereService $matiereService
    ) {}

    /** Readable by both AGRICOLE and FOURNISSEUR */
    #[Route('', name: 'index')]
    public function index(ProduitRepository $repo): Response
    {
        return $this->render('erp/produit/index.html.twig', [
            'produits' => $repo->findAllWithRecette(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_AGRICOLE')]
    public function new(Request $request): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($produit);
            $this->em->flush();
            $this->addFlash('success', 'Produit créé avec sa recette.');
            return $this->redirectToRoute('erp_produit_index');
        }

        return $this->render('erp/produit/form.html.twig', ['form' => $form, 'title' => 'Nouveau produit']);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_AGRICOLE')]
    public function edit(int $id, Request $request, ProduitRepository $repo): Response
    {
        $produit = $repo->find($id);
        if (!$produit) {
            $this->addFlash('error', 'Produit introuvable.');
            return $this->redirectToRoute('erp_produit_index');
        }

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Produit mis à jour.');
            return $this->redirectToRoute('erp_produit_index');
        }

        return $this->render('erp/produit/form.html.twig', ['form' => $form, 'title' => 'Modifier ' . $produit->getNom()]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    #[IsGranted('ROLE_AGRICOLE')]
    public function delete(int $id, Request $request, ProduitRepository $repo): Response
    {
        $produit = $repo->find($id);
        if (!$produit || !$this->isCsrfTokenValid('del_produit_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Action invalide.');
            return $this->redirectToRoute('erp_produit_index');
        }
        try {
            $this->em->remove($produit);
            $this->em->flush();
            $this->addFlash('success', 'Produit supprimé.');
        } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
            $this->addFlash('error', 'Impossible : ce produit est référencé dans des ventes.');
        }
        return $this->redirectToRoute('erp_produit_index');
    }

    #[Route('/{id}/produire', name: 'produire', methods: ['POST'])]
    #[IsGranted('ROLE_AGRICOLE')]
    public function produire(int $id, Request $request, ProduitRepository $repo): Response
    {
        $produit = $repo->find($id);
        if (!$produit || !$this->isCsrfTokenValid('produire_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Action invalide.');
            return $this->redirectToRoute('erp_produit_index');
        }

        if ($produit->isSimple()) {
            $this->addFlash('error', 'Ce produit est simple, pas de production par recette.');
            return $this->redirectToRoute('erp_produit_index');
        }

        $batches = max(1, (int) $request->request->get('batches', 1));
        $conn    = $this->em->getConnection();

        $ingredients = $conn->fetchAllAssociative(
            'SELECT id_matiere, quantite FROM erp_recette_ingredient WHERE id_produit = :id',
            ['id' => $id]
        );

        if (empty($ingredients)) {
            $this->addFlash('error', 'Ce produit n\'a pas de recette définie.');
            return $this->redirectToRoute('erp_produit_index');
        }

        foreach ($ingredients as $ing) {
            $needed = (float) $ing['quantite'] * $batches;
            $stock  = $this->matiereService->getStockById((int) $ing['id_matiere']);
            if ($stock < $needed) {
                $nom = $conn->fetchOne('SELECT nom FROM erp_matiere WHERE id_matiere = :id', ['id' => $ing['id_matiere']]);
                $this->addFlash('error', "Stock insuffisant pour \"{$nom}\": disponible={$stock}, requis={$needed}");
                return $this->redirectToRoute('erp_produit_index');
            }
        }

        foreach ($ingredients as $ing) {
            $consume = (float) $ing['quantite'] * $batches;
            try {
                $this->matiereService->decreaseStock((int) $ing['id_matiere'], $consume);
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('erp_produit_index');
            }
        }

        $produced = $produit->getQuantiteProduite() * $batches;
        $conn->executeStatement(
            'UPDATE erp_produit SET stock = stock + :qty WHERE id_produit = :id',
            ['qty' => $produced, 'id' => $id]
        );

        $this->addFlash('success', sprintf(
            'Production enregistrée : %s lot(s) → +%s unité(s) de "%s".',
            $batches, $produced, $produit->getNom()
        ));
        return $this->redirectToRoute('erp_produit_index');
    }
}
