<?php

namespace App\Entity\ERP;

use App\Repository\ERP\ServiceERPRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceERPRepository::class)]
#[ORM\Table(name: 'erp_service')]
#[ORM\Index(columns: ['stock'], name: 'idx_erp_service_stock')]
class ServiceERP
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_service', type: 'integer')]
    private ?int $idService = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    private string $nom = '';

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'prix', type: 'float', options: ['default' => 0])]
    private float $prix = 0.0;

    #[ORM\Column(name: 'stock', type: 'integer', options: ['default' => 0])]
    private int $stock = 0;

    #[ORM\Column(name: 'seuil_critique', type: 'integer', options: ['default' => 0])]
    private int $seuilCritique = 0;

    public function isStockCritique(): bool
    {
        return $this->stock <= $this->seuilCritique;
    }

    public function getIdService(): ?int { return $this->idService; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getPrix(): float { return $this->prix; }
    public function setPrix(float $prix): static { $this->prix = $prix; return $this; }

    public function getStock(): int { return $this->stock; }
    public function setStock(int $stock): static { $this->stock = $stock; return $this; }

    public function getSeuilCritique(): int { return $this->seuilCritique; }
    public function setSeuilCritique(int $seuilCritique): static { $this->seuilCritique = $seuilCritique; return $this; }
}
