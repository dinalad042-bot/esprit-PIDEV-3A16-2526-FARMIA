<?php

namespace App\Entity\ERP;

use App\Repository\ERP\AchatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/** Purchase order — buying raw materials (Matieres) */
#[ORM\Entity(repositoryClass: AchatRepository::class)]
#[ORM\Table(name: 'erp_achat')]
#[ORM\Index(columns: ['date_achat'], name: 'idx_erp_achat_date')]
class Achat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_achat', type: 'integer')]
    private ?int $idAchat = null;

    #[ORM\Column(name: 'date_achat', type: 'date')]
    private \DateTimeInterface $dateAchat;

    #[ORM\Column(name: 'total', type: 'float', options: ['default' => 0])]
    private float $total = 0.0;

    #[ORM\Column(name: 'paid', type: 'boolean', options: ['default' => false])]
    private bool $paid = false;

    #[ORM\OneToMany(
        mappedBy: 'achat',
        targetEntity: LigneAchat::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $lignes;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $this->dateAchat = new \DateTimeImmutable();
    }

    public function addLigne(LigneAchat $ligne): void
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setAchat($this);
        }
    }

    public function removeLigne(LigneAchat $ligne): void
    {
        $this->lignes->removeElement($ligne);
    }

    public function getIdAchat(): ?int { return $this->idAchat; }

    public function getDateAchat(): \DateTimeInterface { return $this->dateAchat; }
    public function setDateAchat(\DateTimeInterface $d): static { $this->dateAchat = $d; return $this; }

    public function getTotal(): float { return $this->total; }
    public function setTotal(float $total): static { $this->total = $total; return $this; }

    public function isPaid(): bool { return $this->paid; }
    public function setPaid(bool $paid): static { $this->paid = $paid; return $this; }

    public function getLignes(): Collection { return $this->lignes; }
}
