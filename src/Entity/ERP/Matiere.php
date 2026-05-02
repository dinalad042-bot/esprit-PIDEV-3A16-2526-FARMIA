<?php

namespace App\Entity\ERP;

use App\Repository\ERP\MatiereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Raw material — purchased via Achat, consumed when producing a Produit.
 */
#[ORM\Entity(repositoryClass: MatiereRepository::class)]
#[ORM\Table(name: 'erp_matiere')]
class Matiere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_matiere', type: 'integer')]
    private ?int $idMatiere = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    private string $nom = '';

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'unite', type: 'string', length: 50, options: ['default' => 'unité'])]
    private string $unite = 'unité';

    #[ORM\Column(name: 'stock', type: 'float', options: ['default' => 0])]
    private float $stock = 0.0;

    #[ORM\Column(name: 'prix_unitaire', type: 'float', options: ['default' => 0])]
    private float $prixUnitaire = 0.0;

    #[ORM\Column(name: 'seuil_critique', type: 'float', options: ['default' => 0])]
    private float $seuilCritique = 0.0;

    #[ORM\OneToMany(mappedBy: 'matiere', targetEntity: RecetteIngredient::class, cascade: ['remove'])]
    private Collection $recetteIngredients;

    public function __construct()
    {
        $this->recetteIngredients = new ArrayCollection();
    }

    public function isStockCritique(): bool
    {
        return $this->seuilCritique > 0 && $this->stock <= $this->seuilCritique;
    }

    public function getIdMatiere(): ?int { return $this->idMatiere; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $d): static { $this->description = $d; return $this; }

    public function getUnite(): string { return $this->unite; }
    public function setUnite(string $unite): static { $this->unite = $unite; return $this; }

    public function getStock(): float { return $this->stock; }
    public function setStock(float $stock): static { $this->stock = $stock; return $this; }

    public function getPrixUnitaire(): float { return $this->prixUnitaire; }
    public function setPrixUnitaire(float $p): static { $this->prixUnitaire = $p; return $this; }

    public function getSeuilCritique(): float { return $this->seuilCritique; }
    public function setSeuilCritique(float $s): static { $this->seuilCritique = $s; return $this; }

    public function getRecetteIngredients(): Collection { return $this->recetteIngredients; }
}
