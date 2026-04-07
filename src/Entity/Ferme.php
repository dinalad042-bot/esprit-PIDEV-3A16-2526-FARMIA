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

    // --- NOUVELLES COLONNES POUR LA CARTE ---

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $longitude = null;

    // --- RELATION AVEC L'UTILISATEUR (MODIFIÉE POUR SCÉNARIO B) ---

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'fermes')]
    #[ORM\JoinColumn(
        name: "id_user", 
        referencedColumnName: "id_user", 
        nullable: true,      // CHANGEMENT ICI : accepte les fermes sans propriétaire
        onDelete: "SET NULL" // Si l'utilisateur est supprimé, la ferme reste
    )]
    private ?User $user = null;

    // --- GETTERS ET SETTERS ---

    public function getIdFerme(): ?int { return $this->id_ferme; }

    public function getNomFerme(): ?string { return $this->nom_ferme; }
    public function setNomFerme(?string $nom_ferme): static { $this->nom_ferme = $nom_ferme; return $this; }

    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(?string $lieu): static { $this->lieu = $lieu; return $this; }

    public function getSurface(): ?float { return $this->surface; }
    public function setSurface(?float $surface): static { $this->surface = $surface; return $this; }

    public function getLatitude(): ?float { return $this->latitude; }
    public function setLatitude(?float $latitude): static { $this->latitude = $latitude; return $this; }

    public function getLongitude(): ?float { return $this->longitude; }
    public function setLongitude(?float $longitude): static { $this->longitude = $longitude; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }
}