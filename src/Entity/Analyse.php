<?php

namespace App\Entity;

use App\Repository\AnalyseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AnalyseRepository::class)]
#[ORM\Table(name: 'analyse')]
#[ORM\HasLifecycleCallbacks]
class Analyse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_analyse', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'date_analyse', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateAnalyse = null;

    #[ORM\Column(name: 'resultat_technique', type: 'text', nullable: true)]
    #[Assert\Length(
        min: 10,
        minMessage: 'Le résultat technique doit contenir au moins {{ limit }} caractères.'
    )]
    private ?string $resultatTechnique = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'analyses')]
    #[ORM\JoinColumn(name: 'id_technicien', referencedColumnName: 'id_user', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Veuillez sélectionner un technicien.')]
    private ?User $technicien = null;

    #[ORM\ManyToOne(targetEntity: Ferme::class, inversedBy: 'analyses')]
    #[ORM\JoinColumn(name: 'id_ferme', referencedColumnName: 'id_ferme', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Veuillez sélectionner une ferme.')]
    private ?Ferme $ferme = null;

    #[ORM\Column(name: 'image_url', type: 'string', length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\OneToMany(
        mappedBy: 'analyse',
        targetEntity: Conseil::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $conseils;

    public function __construct()
    {
        $this->conseils    = new ArrayCollection();
        $this->dateAnalyse = new \DateTime();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if ($this->dateAnalyse === null) {
            $this->dateAnalyse = new \DateTime();
        }
    }

    public function getId(): ?int { return $this->id; }

    public function getDateAnalyse(): ?\DateTimeInterface { return $this->dateAnalyse; }
    public function setDateAnalyse(?\DateTimeInterface $d): static { $this->dateAnalyse = $d; return $this; }

    public function getResultatTechnique(): ?string { return $this->resultatTechnique; }
    public function setResultatTechnique(?string $r): static { $this->resultatTechnique = $r; return $this; }

    public function getTechnicien(): ?User { return $this->technicien; }
    public function setTechnicien(?User $t): static { $this->technicien = $t; return $this; }

    public function getFerme(): ?Ferme { return $this->ferme; }
    public function setFerme(?Ferme $f): static { $this->ferme = $f; return $this; }

    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function setImageUrl(?string $u): static { $this->imageUrl = $u; return $this; }

    public function getConseils(): Collection { return $this->conseils; }

    public function addConseil(Conseil $c): static
    {
        if (!$this->conseils->contains($c)) {
            $this->conseils->add($c);
            $c->setAnalyse($this);
        }
        return $this;
    }

    public function removeConseil(Conseil $c): static
    {
        if ($this->conseils->removeElement($c)) {
            if ($c->getAnalyse() === $this) {
                $c->setAnalyse(null);
            }
        }
        return $this;
    }

    public function getNbConseils(): int { return $this->conseils->count(); }
}