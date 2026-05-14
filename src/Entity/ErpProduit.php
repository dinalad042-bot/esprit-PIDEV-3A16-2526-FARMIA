<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'erp_produit')]
class ErpProduit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_produit', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    private ?string $nom = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'prix_vente', type: 'float')]
    private float $prixVente = 0;

    #[ORM\Column(name: 'quantite_produite', type: 'float')]
    private float $quantiteProduite = 1;

    #[ORM\Column(name: 'stock', type: 'float')]
    private float $stock = 0;

    #[ORM\Column(name: 'is_simple', type: 'boolean')]
    private bool $isSimple = false;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: ErpRecetteIngredient::class)]
    private Collection $recetteIngredients;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: ErpLigneVente::class)]
    private Collection $lignesVente;

    public function __construct()
    {
        $this->recetteIngredients = new ArrayCollection();
        $this->lignesVente = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrixVente(): float
    {
        return $this->prixVente;
    }

    public function setPrixVente(float $prixVente): static
    {
        $this->prixVente = $prixVente;
        return $this;
    }

    public function getQuantiteProduite(): float
    {
        return $this->quantiteProduite;
    }

    public function setQuantiteProduite(float $quantiteProduite): static
    {
        $this->quantiteProduite = $quantiteProduite;
        return $this;
    }

    public function getStock(): float
    {
        return $this->stock;
    }

    public function setStock(float $stock): static
    {
        $this->stock = $stock;
        return $this;
    }

    public function isSimple(): bool
    {
        return $this->isSimple;
    }

    public function setIsSimple(bool $isSimple): static
    {
        $this->isSimple = $isSimple;
        return $this;
    }

    /**
     * @return Collection<int, ErpRecetteIngredient>
     */
    public function getRecetteIngredients(): Collection
    {
        return $this->recetteIngredients;
    }

    public function addRecetteIngredient(ErpRecetteIngredient $recetteIngredient): static
    {
        if (!$this->recetteIngredients->contains($recetteIngredient)) {
            $this->recetteIngredients->add($recetteIngredient);
            $recetteIngredient->setProduit($this);
        }

        return $this;
    }

    public function removeRecetteIngredient(ErpRecetteIngredient $recetteIngredient): static
    {
        if ($this->recetteIngredients->removeElement($recetteIngredient)) {
            if ($recetteIngredient->getProduit() === $this) {
                $recetteIngredient->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ErpLigneVente>
     */
    public function getLignesVente(): Collection
    {
        return $this->lignesVente;
    }

    public function addLigneVente(ErpLigneVente $ligneVente): static
    {
        if (!$this->lignesVente->contains($ligneVente)) {
            $this->lignesVente->add($ligneVente);
            $ligneVente->setProduit($this);
        }

        return $this;
    }

    public function removeLigneVente(ErpLigneVente $ligneVente): static
    {
        if ($this->lignesVente->removeElement($ligneVente)) {
            if ($ligneVente->getProduit() === $this) {
                $ligneVente->setProduit(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}