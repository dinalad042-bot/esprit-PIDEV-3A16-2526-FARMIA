<?php

namespace App\Entity;

use App\Repository\FermeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FermeRepository::class)]
class Ferme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_ferme")]
    private ?int $id_ferme = null;

    #[ORM\Column(name: "nom_ferme", length: 255)]
    private ?string $nom_ferme = null;

    #[ORM\Column(name: "lieu", length: 255)]
    private ?string $lieu = null;

    #[ORM\Column(name: "surface")]
    private ?float $surface = null;

    // Getter pour l'ID (utilisé par Twig pour les liens)
    public function getIdFerme(): ?int { return $this->id_ferme; }

    // Getters et Setters synchronisés
    public function getNomFerme(): ?string { return $this->nom_ferme; }
    public function setNomFerme(string $nom_ferme): static { $this->nom_ferme = $nom_ferme; return $this; }

    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(string $lieu): static { $this->lieu = $lieu; return $this; }

    public function getSurface(): ?float { return $this->surface; }
    public function setSurface(float $surface): static { $this->surface = $surface; return $this; }
}