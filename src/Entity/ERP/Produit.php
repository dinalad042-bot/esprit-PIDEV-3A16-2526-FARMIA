<?php

namespace App\Entity\ERP;

use App\Repository\ERP\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Table(name: 'erp_produit')]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_produit', type: 'integer')]
    private ?int $idProduit = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    private string $nom = '';

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'prix_vente', type: 'float', options: ['default' => 0])]
    private float $prixVente = 0.0;

    /**
     * How many units of this product are produced per recipe execution.
     * e.g. recipe uses 3 wood + 2 metal → produces 2 chairs → quantiteProduite = 2
     * For simple products (no recipe), this is the stock managed manually.
     */
    #[ORM\Column(name: 'quantite_produite', type: 'float', options: ['default' => 1])]
    private float $quantiteProduite = 1.0;

    /**
     * Current available stock of this finished product.
     * Increases when a production batch is recorded, decreases on sale.
     */
    #[ORM\Column(name: 'stock', type: 'float', options: ['default' => 0])]
    private float $stock = 0.0;

    #[ORM\Column(name: 'seuil_critique', type: 'float', options: ['default' => 0])]
    private float $seuilCritique = 0.0;

    /**
     * If true: simple product sold directly (eggs, milk…), no recipe needed.
     * Stock is managed manually via Achat or direct entry.
     * If false: produced from raw materials using the recipe.
     */
    #[ORM\Column(name: 'is_simple', type: 'boolean', options: ['default' => false])]
    private bool $isSimple = false;

    #[ORM\OneToMany(
        mappedBy: 'produit',
        targetEntity: RecetteIngredient::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $recette;

    public function __construct()
    {
        $this->recette = new ArrayCollection();
    }

    // Symfony Form CollectionType with by_reference:false needs addRecette/removeRecette
    public function addRecette(RecetteIngredient $ingredient): void
    {
        if (!$this->recette->contains($ingredient)) {
            $this->recette->add($ingredient);
            $ingredient->setProduit($this);
        }
    }

    public function removeRecette(RecetteIngredient $ingredient): void
    {
        $this->recette->removeElement($ingredient);
    }

    public function getIdProduit(): ?int { return $this->idProduit; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $d): static { $this->description = $d; return $this; }

    public function getPrixVente(): float { return $this->prixVente; }
    public function setPrixVente(float $p): static { $this->prixVente = $p; return $this; }

    public function getQuantiteProduite(): float { return $this->quantiteProduite; }
    public function setQuantiteProduite(float $q): static { $this->quantiteProduite = $q; return $this; }

    public function getStock(): float { return $this->stock; }
    public function setStock(float $stock): static { $this->stock = $stock; return $this; }

    public function getSeuilCritique(): float { return $this->seuilCritique; }
    public function setSeuilCritique(float $s): static { $this->seuilCritique = $s; return $this; }

    public function isStockCritique(): bool
    {
        return $this->seuilCritique > 0 && $this->stock <= $this->seuilCritique;
    }

    public function isSimple(): bool { return $this->isSimple; }
    public function setIsSimple(bool $isSimple): static { $this->isSimple = $isSimple; return $this; }

    public function getRecette(): Collection { return $this->recette; }
}
