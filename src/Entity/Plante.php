<?php

namespace App\Entity;

use App\Repository\PlanteRepository;
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

    #[ORM\Column(name: "id_ferme")]
    #[Assert\NotNull(message: "Veuillez sélectionner une ferme.")]
    private ?int $id_ferme = null;

    #[ORM\Column(name: "quantite")]
    #[Assert\NotBlank(message: "La quantité est obligatoire.")]
    #[Assert\Positive(message: "La quantité doit être un nombre supérieur à 0.")]
    private ?int $quantite = null;

    public function getIdPlante(): ?int 
    { 
        return $this->id_plante; 
    }

    public function getNomEspece(): ?string 
    { 
        return $this->nom_espece; 
    }

    // Ajout du ? pour accepter null si le formulaire est vide
    public function setNomEspece(?string $nom_espece): static 
    { 
        $this->nom_espece = $nom_espece; 
        return $this; 
    }

    public function getCycleVie(): ?string 
    { 
        return $this->cycle_vie; 
    }

    // Ajout du ? pour accepter null
    public function setCycleVie(?string $cycle_vie): static 
    { 
        $this->cycle_vie = $cycle_vie; 
        return $this; 
    }

    public function getIdFerme(): ?int 
    { 
        return $this->id_ferme; 
    }

    // Correction CRUCIALE : Ajout du ? devant int
    public function setIdFerme(?int $id_ferme): static 
    { 
        $this->id_ferme = $id_ferme; 
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
}