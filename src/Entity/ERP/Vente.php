<?php

namespace App\Entity\ERP;

use App\Repository\ERP\VenteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/** Sale order — selling finished products (Produits), consuming raw materials via recipe */
#[ORM\Entity(repositoryClass: VenteRepository::class)]
#[ORM\Table(name: 'erp_vente')]
class Vente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_vente', type: 'integer')]
    private ?int $idVente = null;

    #[ORM\Column(name: 'date_vente', type: 'date')]
    private \DateTimeInterface $dateVente;

    #[ORM\Column(name: 'total', type: 'float', options: ['default' => 0])]
    private float $total = 0.0;

    #[ORM\OneToMany(
        mappedBy: 'vente',
        targetEntity: LigneVente::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $lignes;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $this->dateVente = new \DateTimeImmutable();
    }

    public function addLigne(LigneVente $ligne): void
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setVente($this);
        }
    }

    public function removeLigne(LigneVente $ligne): void
    {
        $this->lignes->removeElement($ligne);
    }

    public function getIdVente(): ?int { return $this->idVente; }

    public function getDateVente(): \DateTimeInterface { return $this->dateVente; }
    public function setDateVente(\DateTimeInterface $d): static { $this->dateVente = $d; return $this; }

    public function getTotal(): float { return $this->total; }
    public function setTotal(float $total): static { $this->total = $total; return $this; }

    public function getLignes(): Collection { return $this->lignes; }
}
