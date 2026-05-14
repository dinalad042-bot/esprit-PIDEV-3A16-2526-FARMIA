<?php

namespace App\Service\ERP;

use App\Entity\ERP\Vente;
use App\Repository\ERP\MatiereRepository;
use App\Repository\ERP\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class VenteService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MatiereService $matiereService,
        private ERPEmailService $emailService,
        private MatiereRepository $matiereRepository,
        private ProduitRepository $produitRepository,
        private Security $security,
        private LoggerInterface $logger
    ) {}

    private function getProduitFromDb(int $idProduit): ?array
    {
        $row = $this->em->getConnection()->fetchAssociative(
            'SELECT id_produit, nom, stock, quantite_produite, is_simple FROM erp_produit WHERE id_produit = :id',
            ['id' => $idProduit]
        );
        return $row ?: null;
    }

    /**
     * Validates that the produit fini has enough stock to cover the sale.
     * Both simple and manufactured products are checked the same way:
     * stock of the finished product must be >= quantity sold.
     *
     * Matieres are NOT checked here — they were already consumed during Produire.
     */
    public function validateStock(Vente $vente): void
    {
        foreach ($vente->getLignes() as $ligne) {
            $idProduit  = $ligne->getProduit()->getIdProduit();
            $qty        = $ligne->getQuantite();
            $produitRow = $this->getProduitFromDb($idProduit);

            if (!$produitRow) {
                throw new \RuntimeException("Produit introuvable: {$idProduit}");
            }

            $stock = (float) $produitRow['stock'];
            if ($stock < $qty) {
                throw new \RuntimeException(
                    "Stock insuffisant pour \"{$produitRow['nom']}\": disponible={$stock}, demandé={$qty}. "
                    . "Utilisez le bouton \"Produire\" pour fabriquer ce produit d'abord."
                );
            }
        }
    }

    public function createVente(Vente $vente): Vente
    {
        $this->validateStock($vente);

        $total = 0.0;
        foreach ($vente->getLignes() as $ligne) {
            $total += $ligne->getSousTotal();
        }
        $vente->setTotal($total);

        $conn = $this->em->getConnection();
        $conn->beginTransaction();
        try {
            $this->em->persist($vente);
            $this->em->flush();
            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw new \RuntimeException("Erreur création vente: " . $e->getMessage(), 0, $e);
        }

        $recipientEmail = $this->getRecipientEmail();
        $recipientName  = $this->getRecipientName();

        // Post-commit: decrease produit fini stock only
        foreach ($vente->getLignes() as $ligne) {
            $idProduit = $ligne->getProduit()->getIdProduit();
            $qty       = $ligne->getQuantite();

            $affected = $conn->executeStatement(
                'UPDATE erp_produit SET stock = stock - :qty WHERE id_produit = :id AND stock >= :qty',
                ['qty' => $qty, 'id' => $idProduit]
            );

            if ($affected === 0) {
                $this->logger->error('[ERP] Produit stock insufficient on commit (race condition?)', [
                    'id' => $idProduit, 'qty' => $qty
                ]);
            }

            // Check produit stock alerts
            if ($recipientEmail) {
                $pRow = $conn->fetchAssociative(
                    'SELECT nom, stock FROM erp_produit WHERE id_produit = :id', ['id' => $idProduit]
                );
                if ($pRow) {
                    $newStock = (float) $pRow['stock'];
                    $nom      = (string) $pRow['nom'];
                    $this->logger->info('[ERP] Produit stock after vente', ['produit' => $nom, 'stock' => $newStock]);

                    try {
                        if ($newStock <= 0) {
                            $this->emailService->sendStockZeroAlert($nom, $idProduit, $recipientEmail, $recipientName);
                            $this->logger->info('[ERP] Zero-stock email sent for produit', ['produit' => $nom]);
                        }
                    } catch (\Throwable $e) {
                        $this->logger->error('[ERP] Email alert failed', ['produit' => $nom, 'error' => $e->getMessage()]);
                    }
                }
            }
        }

        return $vente;
    }

    public function deleteVente(int $idVente): void
    {
        $vente = $this->em->find(Vente::class, $idVente);
        if (!$vente) throw new \RuntimeException("Vente introuvable: {$idVente}");

        // Collect produit stock to restore
        $toRestore = []; // [idProduit => qty]
        foreach ($vente->getLignes() as $ligne) {
            $idProduit = $ligne->getProduit()->getIdProduit();
            $toRestore[$idProduit] = ($toRestore[$idProduit] ?? 0) + $ligne->getQuantite();
        }

        $conn = $this->em->getConnection();
        $conn->beginTransaction();
        try {
            $this->em->remove($vente);
            $this->em->flush();
            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw new \RuntimeException("Erreur suppression vente", 0, $e);
        }

        // Restore produit fini stock only
        foreach ($toRestore as $idProduit => $qty) {
            try {
                $conn->executeStatement(
                    'UPDATE erp_produit SET stock = stock + :qty WHERE id_produit = :id',
                    ['qty' => $qty, 'id' => $idProduit]
                );
            } catch (\Throwable $e) {
                $this->logger->error('[ERP] Produit stock restore failed on vente delete', ['id' => $idProduit]);
            }
        }
    }

    private function getRecipientEmail(): ?string
    {
        $user = $this->security->getUser();
        return ($user && method_exists($user, 'getEmail')) ? $user->getEmail() : null;
    }

    private function getRecipientName(): string
    {
        $user = $this->security->getUser();
        if (!$user) return '';
        $parts = [];
        if (method_exists($user, 'getPrenom') && $user->getPrenom()) $parts[] = $user->getPrenom();
        if (method_exists($user, 'getNom') && $user->getNom()) $parts[] = $user->getNom();
        return implode(' ', $parts);
    }
}
