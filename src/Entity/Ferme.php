<?php

namespace App\Entity;

use App\Repository\FermeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FermeRepository::class)]
class Ferme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_ferme")]
    private ?int $id_ferme = null;

    #[ORM\Column(name: "nom_ferme", length: 255)]
    #[Assert\NotBlank(message: "Le nom de la ferme est obligatoire.")]
    #[Assert\Length(min: 3, minMessage: "Le nom doit contenir au moins {{ limit }} caractères.")]
    private ?string $nom_ferme = null;

    #[ORM\Column(name: "lieu", length: 255)]
    #[Assert\NotBlank(message: "La localisation (lieu) est obligatoire.")]
    private ?string $lieu = null;

    #[ORM\Column(name: "surface")]
    #[Assert\NotBlank(message: "La surface est obligatoire.")]
    #[Assert\Positive(message: "La surface doit être supérieure à 0.")]
    private ?float $surface = null;

    public function getIdFerme(): ?int { return $this->id_ferme; }

    public function getNomFerme(): ?string { return $this->nom_ferme; }
    public function setNomFerme(?string $nom_ferme): static { $this->nom_ferme = $nom_ferme; return $this; }

    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(?string $lieu): static { $this->lieu = $lieu; return $this; }

    public function getSurface(): ?float { return $this->surface; }
    public function setSurface(?float $surface): static { $this->surface = $surface; return $this; }
}