<?php

namespace App\Service;

use App\Entity\Analyse;

class AnalyseManager
{
    public function validate(Analyse $analyse): bool
    {
        // Règle 1 : description obligatoire
        if (empty($analyse->getDescriptionDemande())) {
            throw new \InvalidArgumentException(
                'La description de la demande est obligatoire'
            );
        }

        // Règle 2 : statut valide
        $statutsValides = ['en_attente', 'en_cours', 'terminee', 'annulee'];
        if (!in_array($analyse->getStatut(), $statutsValides)) {
            throw new \InvalidArgumentException(
                'Le statut est invalide'
            );
        }

        // Règle 3 : score de confiance IA (si renseigné)
        if ($analyse->getAiConfidenceScore() !== null) {
            $score = (int) $analyse->getAiConfidenceScore();
            if ($score < 0 || $score > 100) {
                throw new \InvalidArgumentException(
                    'Le score de confiance doit être entre 0 et 100'
                );
            }
        }

        return true;
    }
}
