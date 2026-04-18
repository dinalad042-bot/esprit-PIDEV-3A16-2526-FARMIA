<?php

namespace App\Entity;

use App\Repository\ArrosageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArrosageRepository::class)]
class Arrosage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_arrosage")] // Pour rester cohérent avec ton id_plante
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateArrosage = null;

    #[ORM\ManyToOne(targetEntity: Plante::class, inversedBy: 'arrosages')]
    #[ORM\JoinColumn(name: "id_plante", referencedColumnName: "id_plante", nullable: false)]
    private ?Plante $plante = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateArrosage(): ?\DateTimeInterface
    {
        return $this->dateArrosage;
    }

    public function setDateArrosage(\DateTimeInterface $dateArrosage): static
    {
        $this->dateArrosage = $dateArrosage;
        return $this;
    }

    public function getPlante(): ?Plante
    {
        return $this->plante;
    }

    public function setPlante(?Plante $plante): static
    {
        $this->plante = $plante;
        return $this;
    }
}