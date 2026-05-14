<?php

namespace App\Entity\ERP;

use App\Repository\ERP\RecetteIngredientRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * One ingredient in a product recipe.
 * e.g. "1 Chair needs 3 Wood + 2 Metal"
 * → RecetteIngredient(produit=Chair, matiere=Wood, quantite=3)
 * → RecetteIngredient(produit=Chair, matiere=Metal, quantite=2)
 */
#[ORM\Entity(repositoryClass: RecetteIngredientRepository::class)]
#[ORM\Table(name: 'erp_recette_ingredient')]
#[ORM\UniqueConstraint(name: 'uk_recette', columns: ['id_produit', 'id_matiere'])]
class RecetteIngredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Produit::class, inversedBy: 'recette')]
    #[ORM\JoinColumn(name: 'id_produit', referencedColumnName: 'id_produit', nullable: false, onDelete: 'CASCADE')]
    private Produit $produit;

    #[ORM\ManyToOne(targetEntity: Matiere::class, inversedBy: 'recetteIngredients')]
    #[ORM\JoinColumn(name: 'id_matiere', referencedColumnName: 'id_matiere', nullable: false, onDelete: 'RESTRICT')]
    private Matiere $matiere;

    /** How many units of this matiere are needed to produce 1 unit of the produit */
    #[ORM\Column(name: 'quantite', type: 'float')]
    private float $quantite = 1.0;

    public function getId(): ?int { return $this->id; }

    public function getProduit(): Produit { return $this->produit; }
    public function setProduit(Produit $produit): static { $this->produit = $produit; return $this; }

    public function getMatiere(): Matiere { return $this->matiere; }
    public function setMatiere(Matiere $matiere): static { $this->matiere = $matiere; return $this; }

    public function getQuantite(): float { return $this->quantite; }
    public function setQuantite(float $quantite): static { $this->quantite = $quantite; return $this; }
}
