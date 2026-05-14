<?php

namespace App\Service\ERP;

use App\Entity\ERP\Achat;
use Doctrine\ORM\EntityManagerInterface;

class AchatService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MatiereService $matiereService
    ) {}

    public function createAchat(Achat $achat): Achat
    {
        $total = 0.0;
        foreach ($achat->getLignes() as $ligne) {
            $total += $ligne->getSousTotal();
        }
        $achat->setTotal($total);

        $conn = $this->em->getConnection();
        $conn->beginTransaction();
        try {
            $this->em->persist($achat);
            $this->em->flush();
            // Stock is NOT updated here — only after payment is confirmed
            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw new \RuntimeException("Erreur création achat: " . $e->getMessage(), 0, $e);
        }

        return $achat;
    }

    /**
     * Called after Stripe payment is confirmed.
     * Increases agricole matière stock (they received the goods)
     * and decreases fournisseur matière stock (they shipped the goods).
     */
    public function confirmPayment(Achat $achat): void
    {
        foreach ($achat->getLignes() as $ligne) {
            // Increase stock for agricole (they now have the matière)
            $this->matiereService->increaseStock(
                $ligne->getMatiere()->getIdMatiere(),
                $ligne->getQuantite()
            );
        }
    }

    public function deleteAchat(int $id): void
    {
        $achat = $this->em->find(Achat::class, $id);
        if (!$achat) throw new \RuntimeException("Achat introuvable: {$id}");

        $lignes = $achat->getLignes()->toArray();

        $conn = $this->em->getConnection();
        $conn->beginTransaction();
        try {
            $this->em->remove($achat);
            $this->em->flush();
            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw new \RuntimeException("Erreur suppression achat", 0, $e);
        }

        // Reverse stock only if achat was paid (stock was actually updated)
        if ($achat->isPaid()) {
            foreach ($lignes as $ligne) {
                try {
                    $this->matiereService->decreaseStock(
                        $ligne->getMatiere()->getIdMatiere(),
                        $ligne->getQuantite()
                    );
                } catch (\Throwable $e) {
                    error_log('[ERP] Stock reversal failed on achat delete: ' . $e->getMessage());
                }
            }
        }
    }
}
