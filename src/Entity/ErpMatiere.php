<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'erp_matiere')]
class ErpMatiere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_matiere', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    private ?string $nom = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'unite', type: 'string', length: 50)]
    private string $unite = 'unité';

    #[ORM\Column(name: 'stock', type: 'float')]
    private float $stock = 0;

    #[ORM\Column(name: 'prix_unitaire', type: 'decimal', precision: 10, scale: 2)]
    private string $prixUnitaire = '0.00';

    #[ORM\Column(name: 'seuil_critique', type: 'float')]
    private float $seuilCritique = 0;

    #[ORM\OneToMany(mappedBy: 'matiere', targetEntity: ErpRecetteIngredient::class)]
    private Collection $recetteIngredients;

    #[ORM\OneToMany(mappedBy: 'matiere', targetEntity: ErpLigneAchat::class)]
    private Collection $lignesAchat;

    public function __construct()
    {
        $this->recetteIngredients = new ArrayCollection();
        $this->lignesAchat = new ArrayCollection();
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

    public function getUnite(): string
    {
        return $this->unite;
    }

    public function setUnite(string $unite): static
    {
        $this->unite = $unite;
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

    public function getPrixUnitaire(): string
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(string $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;
        return $this;
    }

    public function getSeuilCritique(): float
    {
        return $this->seuilCritique;
    }

    public function setSeuilCritique(float $seuilCritique): static
    {
        $this->seuilCritique = $seuilCritique;
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
            $recetteIngredient->setMatiere($this);
        }

        return $this;
    }

    public function removeRecetteIngredient(ErpRecetteIngredient $recetteIngredient): static
    {
        if ($this->recetteIngredients->removeElement($recetteIngredient)) {
            if ($recetteIngredient->getMatiere() === $this) {
                $recetteIngredient->setMatiere(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ErpLigneAchat>
     */
    public function getLignesAchat(): Collection
    {
        return $this->lignesAchat;
    }

    public function addLigneAchat(ErpLigneAchat $ligneAchat): static
    {
        if (!$this->lignesAchat->contains($ligneAchat)) {
            $this->lignesAchat->add($ligneAchat);
            $ligneAchat->setMatiere($this);
        }

        return $this;
    }

    public function removeLigneAchat(ErpLigneAchat $ligneAchat): static
    {
        if ($this->lignesAchat->removeElement($ligneAchat)) {
            if ($ligneAchat->getMatiere() === $this) {
                $ligneAchat->setMatiere(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}