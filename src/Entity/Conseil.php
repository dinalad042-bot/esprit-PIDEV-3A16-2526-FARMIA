<?php

namespace App\Entity;

use App\Enum\Priorite;
use App\Repository\ConseilRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConseilRepository::class)]
#[ORM\Table(name: 'conseil')]
class Conseil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_conseil', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'description_conseil', type: 'text')]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Assert\Length(
        min: 10,
        minMessage: 'La description doit contenir au moins {{ limit }} caractères.'
    )]
    private ?string $descriptionConseil = null;

    #[ORM\Column(
        name: 'priorite',
        type: 'string',
        length: 10,
        nullable: true,
        options: ['default' => 'MOYENNE']
    )]
    #[Assert\NotNull(message: 'La priorité est obligatoire.')]
    private ?string $prioriteRaw = 'MOYENNE';

    #[ORM\ManyToOne(targetEntity: Analyse::class, inversedBy: 'conseils')]
    #[ORM\JoinColumn(name: 'id_analyse', referencedColumnName: 'id_analyse', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: "L'analyse associée est obligatoire.")]
    private ?Analyse $analyse = null;

    public function getId(): ?int { return $this->id; }

    public function getDescriptionConseil(): ?string { return $this->descriptionConseil; }
    public function setDescriptionConseil(string $d): static { $this->descriptionConseil = $d; return $this; }

    // Returns typed Enum
    public function getPriorite(): ?Priorite
    {
        return $this->prioriteRaw ? Priorite::from($this->prioriteRaw) : null;
    }

    // Accepts Enum or string
    public function setPriorite(Priorite|string|null $p): static
    {
        if ($p instanceof Priorite) {
            $this->prioriteRaw = $p->value;
        } else {
            $this->prioriteRaw = $p;
        }
        return $this;
    }

    public function getPrioriteRaw(): ?string { return $this->prioriteRaw; }

    public function getAnalyse(): ?Analyse { return $this->analyse; }
    public function setAnalyse(?Analyse $a): static { $this->analyse = $a; return $this; }
}