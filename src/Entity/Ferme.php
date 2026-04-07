<?php

namespace App\Entity;

use App\Repository\FermeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FermeRepository::class)]
#[ORM\Table(name: 'ferme')]
class Ferme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_ferme', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom_ferme', type: 'string', length: 100)]
    private ?string $nomFerme = null;

    #[ORM\Column(name: 'lieu', type: 'string', length: 255)]
    private ?string $lieu = null;

    #[ORM\Column(name: 'surface', type: 'float', nullable: true, options: ['default' => 0])]
    private ?float $surface = 0;

    #[ORM\Column(name: 'id_fermier', type: 'integer')]
    private ?int $idFermier = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'ferme', targetEntity: Analyse::class)]
    private Collection $analyses;

    public function __construct()
    {
        $this->analyses  = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getNomFerme(): ?string { return $this->nomFerme; }
    public function setNomFerme(string $v): static { $this->nomFerme = $v; return $this; }

    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(string $v): static { $this->lieu = $v; return $this; }

    public function getSurface(): ?float { return $this->surface; }
    public function setSurface(?float $v): static { $this->surface = $v; return $this; }

    public function getIdFermier(): ?int { return $this->idFermier; }
    public function setIdFermier(int $v): static { $this->idFermier = $v; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }

    public function getAnalyses(): Collection { return $this->analyses; }

    public function __toString(): string
    {
        return $this->nomFerme . ' — ' . $this->lieu;
    }
}