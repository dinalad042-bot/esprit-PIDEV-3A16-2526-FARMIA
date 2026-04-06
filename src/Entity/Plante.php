<?php

namespace App\Entity;

use App\Repository\PlanteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanteRepository::class)]
class Plante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_plante")]
    private ?int $id_plante = null;

    #[ORM\Column(name: "nom_espece", length: 255)]
    private ?string $nom_espece = null;

    #[ORM\Column(name: "cycle_vie", length: 255)]
    private ?string $cycle_vie = null;

    #[ORM\Column(name: "id_ferme")]
    private ?int $id_ferme = null;

    #[ORM\Column(name: "quantite")]
    private ?int $quantite = null;

    public function getIdPlante(): ?int { return $this->id_plante; }

    public function getNomEspece(): ?string { return $this->nom_espece; }
    public function setNomEspece(string $nom_espece): static { $this->nom_espece = $nom_espece; return $this; }

    public function getCycleVie(): ?string { return $this->cycle_vie; }
    public function setCycleVie(string $cycle_vie): static { $this->cycle_vie = $cycle_vie; return $this; }

    public function getIdFerme(): ?int { return $this->id_ferme; }
    public function setIdFerme(int $id_ferme): static { $this->id_ferme = $id_ferme; return $this; }

    public function getQuantite(): ?int { return $this->quantite; }
    public function setQuantite(int $quantite): static { $this->quantite = $quantite; return $this; }
}