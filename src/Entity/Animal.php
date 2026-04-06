<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
class Animal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_animal")]
    private ?int $id_animal = null;

    #[ORM\Column(length: 255)]
    private ?string $espece = null;

    #[ORM\Column(name: "etat_sante", length: 255)]
    private ?string $etat_sante = null;

    #[ORM\Column(name: "date_naissance", type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_naissance = null;

    #[ORM\Column(name: "id_ferme")]
    private ?int $id_ferme = null;

    public function getIdAnimal(): ?int { return $this->id_animal; }

    public function getEspece(): ?string { return $this->espece; }
    public function setEspece(string $espece): static { $this->espece = $espece; return $this; }

    // Indispensable pour animal.etatSante dans Twig
    public function getEtatSante(): ?string { return $this->etat_sante; }
    public function setEtatSante(string $etat_sante): static { $this->etat_sante = $etat_sante; return $this; }

    // Indispensable pour animal.dateNaissance dans Twig
    public function getDateNaissance(): ?\DateTimeInterface { return $this->date_naissance; }
    public function setDateNaissance(?\DateTimeInterface $date_naissance): static { $this->date_naissance = $date_naissance; return $this; }

    public function getIdFerme(): ?int { return $this->id_ferme; }
    public function setIdFerme(int $id_ferme): static { $this->id_ferme = $id_ferme; return $this; }
}