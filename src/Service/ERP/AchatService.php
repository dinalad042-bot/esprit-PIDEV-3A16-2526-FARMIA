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

            // Increase matiere stock for each line
            foreach ($achat->getLignes() as $ligne) {
                $this->matiereService->increaseStock(
                    $ligne->getMatiere()->getIdMatiere(),
                    $ligne->getQuantite()
                );
            }

            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw new \RuntimeException("Erreur création achat: " . $e->getMessage(), 0, $e);
        }

        return $achat;
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

        // Reverse stock
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
