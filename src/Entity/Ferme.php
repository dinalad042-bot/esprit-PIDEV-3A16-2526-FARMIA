<?php

namespace App\Entity;

use App\Repository\FermeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $longitude = null;

    // --- RELATION AVEC L'UTILISATEUR ---
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'fermes')]
    #[ORM\JoinColumn(
        name: "id_user", 
        referencedColumnName: "id_user", 
        nullable: true,
        onDelete: "SET NULL"
    )]
    private ?User $user = null;

    // --- CORRECTION : AJOUT DES RELATIONS POUR LA CARTE ---

    #[ORM\OneToMany(mappedBy: 'ferme', targetEntity: Plante::class)]
    private Collection $plantes;

    #[ORM\OneToMany(mappedBy: 'ferme', targetEntity: Animal::class)]
    private Collection $animals;

    public function __construct()
    {
        $this->plantes = new ArrayCollection();
        $this->animals = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Plante>
     */
    public function getPlantes(): Collection
    {
        return $this->plantes;
    }

    public function addPlante(Plante $plante): static
    {
        if (!$this->plantes->contains($plante)) {
            $this->plantes->add($plante);
            $plante->setFerme($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, Animal>
     */
    public function getAnimals(): Collection
    {
        return $this->animals;
    }

    public function addAnimal(Animal $animal): static
    {
        if (!$this->animals->contains($animal)) {
            $this->animals->add($animal);
            $animal->setFerme($this);
        }
        return $this;
    }

    public function removePlante(Plante $plante): static
    {
        if ($this->plantes->removeElement($plante)) {
            // set the owning side to null (unless already changed)
            if ($plante->getFerme() === $this) {
                $plante->setFerme(null);
            }
        }

        return $this;
    }

    public function removeAnimal(Animal $animal): static
    {
        if ($this->animals->removeElement($animal)) {
            // set the owning side to null (unless already changed)
            if ($animal->getFerme() === $this) {
                $animal->setFerme(null);
            }
        }

        return $this;
    }
}