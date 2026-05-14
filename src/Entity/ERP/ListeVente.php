<?php

namespace App\Entity\ERP;

use App\Repository\ERP\ListeVenteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListeVenteRepository::class)]
#[ORM\Table(name: 'erp_liste_vente')]
class ListeVente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vente::class, inversedBy: 'lignes')]
    #[ORM\JoinColumn(name: 'id_vente', referencedColumnName: 'id_vente', nullable: false)]
    private Vente $vente;

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

    public function getVente(): Vente { return $this->vente; }
    public function setVente(Vente $vente): static { $this->vente = $vente; return $this; }

    public function getService(): ServiceERP { return $this->service; }
    public function setService(ServiceERP $service): static { $this->service = $service; return $this; }

    public function getQuantite(): int { return $this->quantite; }
    public function setQuantite(int $quantite): static { $this->quantite = $quantite; return $this; }

    public function getPrixUnitaire(): float { return $this->prixUnitaire; }
    public function setPrixUnitaire(float $prixUnitaire): static { $this->prixUnitaire = $prixUnitaire; return $this; }
}
