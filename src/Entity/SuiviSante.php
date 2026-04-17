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

    // Relation avec l'animal : Si l'animal est supprimé, son historique l'est aussi (on-delete CASCADE)
    #[ORM\ManyToOne(targetEntity: Animal::class)]
    #[ORM\JoinColumn(name: "id_animal", referencedColumnName: "id_animal", nullable: false, onDelete: "CASCADE")]
    private ?Animal $animal = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateConsultation = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $diagnostic = null;

    #[ORM\Column(length: 50)]
    private ?string $etatAuMoment = null;

    /**
     * Le constructeur initialise la date de consultation automatiquement à "maintenant"
     */
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

    public function setEtatAuMoment(string $etatAuMoment): self
    {
        $this->etatAuMoment = $etatAuMoment;
        return $this;
    }
}