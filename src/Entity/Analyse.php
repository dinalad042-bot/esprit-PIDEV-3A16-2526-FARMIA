<?php

namespace App\Entity;

use App\Enum\StatutAnalyse;
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
    #[ORM\JoinColumn(name: 'id_technicien', referencedColumnName: 'id_user', nullable: true, onDelete: 'SET NULL')]
    private ?User $technicien = null;

    #[ORM\ManyToOne(targetEntity: Ferme::class, inversedBy: 'analyses')]
    #[ORM\JoinColumn(name: 'id_ferme', referencedColumnName: 'id_ferme', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Veuillez sélectionner une ferme.')]
    private ?Ferme $ferme = null;

    #[ORM\Column(name: 'image_url', type: 'string', length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(name: 'statut', type: 'string', length: 20, nullable: false)]
    private string $statut = 'en_attente';

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_demandeur', referencedColumnName: 'id_user', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Veuillez sélectionner un demandeur.')]
    private ?User $demandeur = null;

    #[ORM\Column(name: 'description_demande', type: 'text', nullable: true)]
    private ?string $descriptionDemande = null;

    #[ORM\ManyToOne(targetEntity: Animal::class)]
    #[ORM\JoinColumn(name: 'id_animal_cible', referencedColumnName: 'id_animal', nullable: true, onDelete: 'SET NULL')]
    private ?Animal $animalCible = null;

    #[ORM\ManyToOne(targetEntity: Plante::class)]
    #[ORM\JoinColumn(name: 'id_plante_cible', referencedColumnName: 'id_plante', nullable: true, onDelete: 'SET NULL')]
    private ?Plante $planteCible = null;

    #[ORM\OneToMany(
        mappedBy: 'analyse',
        targetEntity: Conseil::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $conseils;

    // AI Diagnosis Fields
    #[ORM\Column(name: 'ai_diagnosis_result', type: 'text', nullable: true)]
    private ?string $aiDiagnosisResult = null;

    #[ORM\Column(name: 'ai_diagnosis_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $aiDiagnosisDate = null;

    #[ORM\Column(name: 'ai_confidence_score', type: 'string', length: 20, nullable: true)]
    private ?string $aiConfidenceScore = null;

    #[ORM\Column(name: 'diagnosis_mode', type: 'string', length: 20, nullable: true)]
    private ?string $diagnosisMode = null;

    public function __construct()
    {
        $this->conseils    = new ArrayCollection();
        $this->dateAnalyse = new \DateTime();
        $this->statut      = 'en_attente';
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

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getStatutEnum(): StatutAnalyse
    {
        return StatutAnalyse::tryFrom($this->statut) ?? StatutAnalyse::EN_ATTENTE;
    }

    public function getDemandeur(): ?User { return $this->demandeur; }
    public function setDemandeur(?User $demandeur): static { $this->demandeur = $demandeur; return $this; }

    public function getDescriptionDemande(): ?string { return $this->descriptionDemande; }
    public function setDescriptionDemande(?string $description): static { $this->descriptionDemande = $description; return $this; }

    public function getAnimalCible(): ?Animal { return $this->animalCible; }
    public function setAnimalCible(?Animal $animal): static { $this->animalCible = $animal; return $this; }

    public function getPlanteCible(): ?Plante { return $this->planteCible; }
    public function setPlanteCible(?Plante $plante): static { $this->planteCible = $plante; return $this; }

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

    // AI Diagnosis Methods
    public function getAiDiagnosisResult(): ?string { return $this->aiDiagnosisResult; }
    public function setAiDiagnosisResult(?string $result): static { $this->aiDiagnosisResult = $result; return $this; }

    public function getAiDiagnosisDate(): ?\DateTimeInterface { return $this->aiDiagnosisDate; }
    public function setAiDiagnosisDate(?\DateTimeInterface $date): static { $this->aiDiagnosisDate = $date; return $this; }

    public function getAiConfidenceScore(): ?string { return $this->aiConfidenceScore; }
    public function setAiConfidenceScore(?string $score): static { $this->aiConfidenceScore = $score; return $this; }

    public function hasAiDiagnosis(): bool { return $this->aiDiagnosisResult !== null; }

    // Diagnosis Mode Methods
    public function getDiagnosisMode(): ?string { return $this->diagnosisMode; }
    public function setDiagnosisMode(?string $mode): static { $this->diagnosisMode = $mode; return $this; }
}
