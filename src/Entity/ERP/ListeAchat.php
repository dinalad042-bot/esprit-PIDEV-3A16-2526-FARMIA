<?php

namespace App\Entity\ERP;

use App\Repository\ERP\ListeAchatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListeAchatRepository::class)]
#[ORM\Table(name: 'erp_liste_achat')]
#[ORM\UniqueConstraint(name: 'uk_erp_liste_achat', columns: ['id_achat', 'id_service'])]
class ListeAchat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Achat::class, inversedBy: 'lignes')]
    #[ORM\JoinColumn(name: 'id_achat', referencedColumnName: 'id_achat', nullable: false)]
    private Achat $achat;

    #[ORM\ManyToOne(targetEntity: ServiceERP::class)]
    #[ORM\JoinColumn(name: 'id_service', referencedColumnName: 'id_service', nullable: false, onDelete: 'RESTRICT')]
    private ServiceERP $service;

    #[ORM\Column(name: 'quantite', type: 'integer')]
    private int $quantite = 1;

    #[ORM\Column(name: 'prix_unitaire', type: 'float')]
    private float $prixUnitaire = 0.0;

    public function getSousTotal(): float
    {
        return $this->quantite * $this->prixUnitaire;
    }

    public function getId(): ?int { return $this->id; }

    public function getAchat(): Achat { return $this->achat; }
    public function setAchat(Achat $achat): static { $this->achat = $achat; return $this; }

    public function getService(): ServiceERP { return $this->service; }
    public function setService(ServiceERP $service): static { $this->service = $service; return $this; }

    public function getQuantite(): int { return $this->quantite; }
    public function setQuantite(int $quantite): static { $this->quantite = $quantite; return $this; }

    public function getPrixUnitaire(): float { return $this->prixUnitaire; }
    public function setPrixUnitaire(float $prixUnitaire): static { $this->prixUnitaire = $prixUnitaire; return $this; }
}
