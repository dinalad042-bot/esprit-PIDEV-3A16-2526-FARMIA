<?php

namespace App\Entity;

use App\Repository\FermeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FermeRepository::class)]
#[ORM\Table(name: 'ferme')]
class Ferme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_ferme', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom_ferme', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Le nom de la ferme est obligatoire.')]
    #[Assert\Length(min: 3, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.')]
    private ?string $nomFerme = null;

    #[ORM\Column(name: 'lieu', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'La localisation est obligatoire.')]
    private ?string $lieu = null;

    #[ORM\Column(name: 'surface', type: 'float', nullable: true)]
    #[Assert\Positive(message: 'La surface doit être supérieure à 0.')]
    private ?float $surface = 0;

    #[ORM\Column(name: 'latitude', type: 'float', nullable: true)]
    #[Assert\Range(min: -90, max: 90, notInRangeMessage: 'La latitude doit être entre {{ min }} et {{ max }}.')]
    private ?float $latitude = null;

    #[ORM\Column(name: 'longitude', type: 'float', nullable: true)]
    #[Assert\Range(min: -180, max: 180, notInRangeMessage: 'La longitude doit être entre {{ min }} et {{ max }}.')]
    private ?float $longitude = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    // ── RELATION: Ferme → User (owner/fermier) ───────────────────────────────
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'fermes')]
    #[ORM\JoinColumn(
        name: 'id_user',
        referencedColumnName: 'id_user',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    private ?User $user = null;

    // ── RELATION: Ferme → Analyse (your module) ───────────────────────────────
    #[ORM\OneToMany(mappedBy: 'ferme', targetEntity: Analyse::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $analyses;

    // ── RELATION: Ferme → Plante (EMEN's module) ─────────────────────────────
    #[ORM\OneToMany(mappedBy: 'ferme', targetEntity: Plante::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $plantes;

    // ── RELATION: Ferme → Animal (EMEN's module) ─────────────────────────────
    #[ORM\OneToMany(mappedBy: 'ferme', targetEntity: Animal::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $animals;

    public function __construct()
    {
        $this->analyses  = new ArrayCollection();
        $this->plantes   = new ArrayCollection();
        $this->animals   = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // ── PK getters (both — yours + EMEN's controllers need getIdFerme()) ──────
    public function getId(): ?int { return $this->id; }
    public function getIdFerme(): ?int { return $this->id; } // alias for EMEN compatibility

    public function getNomFerme(): ?string { return $this->nomFerme; }
    public function setNomFerme(?string $v): static { $this->nomFerme = $v; return $this; }

    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(?string $v): static { $this->lieu = $v; return $this; }

    public function getSurface(): ?float { return $this->surface; }
    public function setSurface(?float $v): static { $this->surface = $v; return $this; }

    public function getLatitude(): ?float { return $this->latitude; }
    public function setLatitude(?float $v): static { $this->latitude = $v; return $this; }

    public function getLongitude(): ?float { return $this->longitude; }
    public function setLongitude(?float $v): static { $this->longitude = $v; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeInterface $v): static { $this->createdAt = $v; return $this; }

    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeInterface $v): static { $this->updatedAt = $v; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getAnalyses(): Collection { return $this->analyses; }

    public function getPlantes(): Collection { return $this->plantes; }
    public function addPlante(Plante $plante): static
    {
        if (!$this->plantes->contains($plante)) {
            $this->plantes->add($plante);
            $plante->setFerme($this);
        }
        return $this;
    }

    public function getAnimals(): Collection { return $this->animals; }
    public function addAnimal(Animal $animal): static
    {
        if (!$this->animals->contains($animal)) {
            $this->animals->add($animal);
            $animal->setFerme($this);
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->nomFerme . ' — ' . $this->lieu;
    }
}
