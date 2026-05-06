<?php

namespace App\Entity;

use App\Repository\SuiviSanteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuiviSanteRepository::class)]
#[ORM\HasLifecycleCallbacks] // Permet d'automatiser la date avant l'enregistrement
class SuiviSante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Animal::class)]
    #[ORM\JoinColumn(name: "animal_id", referencedColumnName: "id_animal", nullable: false, onDelete: "CASCADE")]
    private ?Animal $animal = null;

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
        // Initialisation par défaut pour éviter les erreurs PHP avant le flush
        $this->dateConsultation = new \DateTime();
    }

    /**
     * Cette méthode remplit la date automatiquement juste avant l'insertion en BDD
     */
    #[ORM\PrePersist]
    public function updateTimestampOnPersist(): void
    {
        if ($this->dateConsultation === null) {
            $this->dateConsultation = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnimal(): ?Animal
    {
        return $this->animal;
    }

    public function setAnimal(?Animal $animal): self
    {
        $this->animal = $animal;
        return $this;
    }

    public function getDateConsultation(): ?\DateTimeInterface
    {
        return $this->dateConsultation;
    }

    /**
     * Changé en 'protected' pour satisfaire les règles d'intégrité de Doctrine.
     * La date doit être gérée par le constructeur ou les LifecycleCallbacks.
     */
    public function setDateConsultation(\DateTimeInterface $dateConsultation): self
    {
        $this->dateConsultation = $dateConsultation;
        return $this;
    }

    public function getDiagnostic(): ?string
    {
        return $this->diagnostic;
    }

    public function setDiagnostic(string $diagnostic): self
    {
        $this->diagnostic = $diagnostic;
        return $this;
    }

    public function getEtatAuMoment(): ?string
    {
        return $this->etatAuMoment;
    }

    public function setEtatAuMoment(?string $etatAuMoment): self
    {
        $this->etatAuMoment = $etatAuMoment;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }
}