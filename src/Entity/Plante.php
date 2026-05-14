<?php

namespace App\Entity;

use App\Repository\PlanteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlanteRepository::class)]
class Plante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_plante")]
    private ?int $id_plante = null;

    #[ORM\Column(name: "nom_espece", length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'espèce ne peut pas être vide.")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le nom de l'espèce doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $nom_espece = null;

    #[ORM\Column(name: "cycle_vie", length: 255)]
    #[Assert\NotBlank(message: "Le cycle de vie est obligatoire.")]
    private ?string $cycle_vie = null;

    #[ORM\ManyToOne(targetEntity: Ferme::class, inversedBy: 'plantes')]
    #[ORM\JoinColumn(name: "id_ferme", referencedColumnName: "id_ferme", nullable: false)]
    private ?Ferme $ferme = null;

    #[ORM\Column(name: "quantite")]
    #[Assert\NotBlank(message: "La quantité est obligatoire.")]
    #[Assert\Positive(message: "La quantité doit être un nombre supérieur à 0.")]
    private ?int $quantite = null;

    // --- AJOUT DE LA RELATION AVEC ARROSAGE ---
    #[ORM\OneToMany(mappedBy: 'plante', targetEntity: Arrosage::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $arrosages;

    public function __construct()
    {
        $this->arrosages = new ArrayCollection();
    }

    public function getIdPlante(): ?int 
    { 
        return $this->id_plante; 
    }

    /**
     * Alias for getIdPlante() to maintain consistency with standard Symfony entity pattern.
     * Allows code to call getId() on Plante just like other entities.
     */
    public function getId(): ?int 
    { 
        return $this->id_plante; 
    }

    public function getNomEspece(): ?string 
    { 
        return $this->nom_espece; 
    }

    public function setNomEspece(?string $nom_espece): static 
    { 
        $this->nom_espece = $nom_espece; 
        return $this; 
    }

    public function getCycleVie(): ?string 
    { 
        return $this->cycle_vie; 
    }

    public function setCycleVie(?string $cycle_vie): static 
    { 
        $this->cycle_vie = $cycle_vie; 
        return $this; 
    }

    public function getFerme(): ?Ferme 
    { 
        return $this->ferme; 
    }

    public function setFerme(?Ferme $ferme): static 
    { 
        $this->ferme = $ferme; 
        return $this; 
    }

    public function getQuantite(): ?int 
    { 
        return $this->quantite; 
    }

    public function setQuantite(?int $quantite): static 
    { 
        $this->quantite = $quantite; 
        return $this; 
    }

    // --- GETTER POUR LES ARROSAGES ---
    /** @return Collection<int, Arrosage> */
    public function getArrosages(): Collection 
    {
        return $this->arrosages;
    }

    public function addArrosage(Arrosage $arrosage): static
    {
        if (!$this->arrosages->contains($arrosage)) {
            $this->arrosages->add($arrosage);
            $arrosage->setPlante($this);
        }
        return $this;
    }

    public function removeArrosage(Arrosage $arrosage): static
    {
        if ($this->arrosages->removeElement($arrosage)) {
            // set the owning side to null (unless already changed)
            if ($arrosage->getPlante() === $this) {
                $arrosage->setPlante(null);
            }
        }
        return $this;
    }
}