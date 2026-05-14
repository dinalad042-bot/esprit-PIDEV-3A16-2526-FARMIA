<?php

namespace App\Entity\ERP;

use App\Repository\ERP\LigneAchatRepository;
use Doctrine\ORM\Mapping as ORM;

/** One line of a purchase order — buying raw materials */
#[ORM\Entity(repositoryClass: LigneAchatRepository::class)]
#[ORM\Table(name: 'erp_ligne_achat')]
#[ORM\UniqueConstraint(name: 'uk_ligne_achat', columns: ['id_achat', 'id_matiere'])]
class LigneAchat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Achat::class, inversedBy: 'lignes')]
    #[ORM\JoinColumn(name: 'id_achat', referencedColumnName: 'id_achat', nullable: false)]
    private Achat $achat;

    #[ORM\ManyToOne(targetEntity: Matiere::class)]
    #[ORM\JoinColumn(name: 'id_matiere', referencedColumnName: 'id_matiere', nullable: false, onDelete: 'RESTRICT')]
    private Matiere $matiere;

    #[ORM\Column(name: 'quantite', type: 'float')]
    private float $quantite = 1.0;

    #[ORM\Column(name: 'prix_unitaire', type: 'float')]
    private float $prixUnitaire = 0.0;

    public function getSousTotal(): float
    {
        return $this->quantite * $this->prixUnitaire;
    }

    public function getId(): ?int { return $this->id; }

    public function getAchat(): Achat { return $this->achat; }
    public function setAchat(Achat $achat): static { $this->achat = $achat; return $this; }

    public function getMatiere(): Matiere { return $this->matiere; }
    public function setMatiere(Matiere $matiere): static { $this->matiere = $matiere; return $this; }

    public function getQuantite(): float { return $this->quantite; }
    public function setQuantite(float $q): static { $this->quantite = $q; return $this; }

    public function getPrixUnitaire(): float { return $this->prixUnitaire; }
    public function setPrixUnitaire(float $p): static { $this->prixUnitaire = $p; return $this; }
}
