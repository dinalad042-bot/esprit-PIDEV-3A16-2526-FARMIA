<?php

namespace App\Entity\ERP;

use App\Repository\ERP\LigneVenteRepository;
use Doctrine\ORM\Mapping as ORM;

/** One line of a sale order — selling finished products */
#[ORM\Entity(repositoryClass: LigneVenteRepository::class)]
#[ORM\Table(name: 'erp_ligne_vente')]
class LigneVente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vente::class, inversedBy: 'lignes')]
    #[ORM\JoinColumn(name: 'id_vente', referencedColumnName: 'id_vente', nullable: false)]
    private Vente $vente;

    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(name: 'id_produit', referencedColumnName: 'id_produit', nullable: false, onDelete: 'RESTRICT')]
    private Produit $produit;

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

    public function getProduit(): Produit { return $this->produit; }
    public function setProduit(Produit $produit): static { $this->produit = $produit; return $this; }

    public function getQuantite(): int { return $this->quantite; }
    public function setQuantite(int $q): static { $this->quantite = $q; return $this; }

    public function getPrixUnitaire(): float { return $this->prixUnitaire; }
    public function setPrixUnitaire(float $p): static { $this->prixUnitaire = $p; return $this; }
}
