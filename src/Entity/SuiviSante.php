<?php

namespace App\Entity;

use App\Repository\SuiviSanteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuiviSanteRepository::class)]
#[ORM\HasLifecycleCallbacks] 
class SuiviSante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Animal::class)]
    #[ORM\JoinColumn(name: "animal_id", referencedColumnName: "id_animal", nullable: false, onDelete: "CASCADE")]
    private ?Animal $animal = null;

    /**
     * CORRECTION : Ajout de la relation avec le suffixe _id pour l'intégrité
     */
#[ORM\ManyToOne(targetEntity: User::class)]
#[ORM\JoinColumn(name: 'performed_by_id', referencedColumnName: 'id_user')]
private ?User $performedBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateConsultation = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $diagnostic = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $etatAuMoment = null;

    #[ORM\Column(length: 30, nullable: true)] 
    private ?string $type = null; 

    public function __construct()
    {
        // CORRECTION : Forçage du fuseau UTC pour aligner PHP et MySQL
        $this->dateConsultation = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Remplit la date automatiquement juste avant l'insertion en BDD en UTC
     */
    #[ORM\PrePersist]
    public function updateTimestampOnPersist(): void
    {
        if ($this->dateConsultation === null) {
            $this->dateConsultation = new \DateTime('now', new \DateTimeZone('UTC'));
        }
    }

    public function getId(): ?int { return $this->id; }

    public function getAnimal(): ?Animal { return $this->animal; }

    public function setAnimal(?Animal $animal): self
    {
        $this->animal = $animal;
        return $this;
    }

    /**
     * Getter et Setter pour performedBy (Correction Integrity)
     */
    public function getPerformedBy(): ?User { return $this->performedBy; }

    public function setPerformedBy(?User $performedBy): self
    {
        $this->performedBy = $performedBy;
        return $this;
    }

    public function getDateConsultation(): ?\DateTimeInterface { return $this->dateConsultation; }

    public function setDateConsultation(\DateTimeInterface $dateConsultation): self
    {
        $this->dateConsultation = $dateConsultation;
        return $this;
    }

    public function getDiagnostic(): ?string { return $this->diagnostic; }

    public function setDiagnostic(string $diagnostic): self
    {
        $this->diagnostic = $diagnostic;
        return $this;
    }

    public function getEtatAuMoment(): ?string { return $this->etatAuMoment; }

    public function setEtatAuMoment(?string $etatAuMoment): self
    {
        $this->etatAuMoment = $etatAuMoment;
        return $this;
    }

    public function getType(): ?string { return $this->type; }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }
}