<?php

namespace App\Entity;

use App\Repository\SuiviSanteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuiviSanteRepository::class)]
class SuiviSante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Animal::class, inversedBy: 'suiviSantes')]
    #[ORM\JoinColumn(name: "id_animal", referencedColumnName: "id_animal", nullable: false, onDelete: "CASCADE")]
    private ?Animal $animal = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateConsultation = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $diagnostic = null;

    #[ORM\Column(length: 50, nullable: true)] // Rendu nullable pour les vaccins
    private ?string $etatAuMoment = null;

    // --- NOUVEAUX CHAMPS POUR LES VACCINS / ACTES MANUELS ---
    
    #[ORM\Column(length: 30, nullable: true)] 
    private ?string $type = null; // Ex: VACCIN, MEDICAMENT, REPRODUCTION, IA

    public function __construct()
    {
        $this->dateConsultation = new \DateTime();
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