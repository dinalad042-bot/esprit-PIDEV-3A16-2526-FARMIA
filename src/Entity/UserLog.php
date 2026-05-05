<?php

namespace App\Entity;

use App\Repository\UserLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserLogRepository::class)]
#[ORM\Table(name: 'user_log')]
class UserLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userLogs')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id_user', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(name: 'action_type', type: 'string', length: 20, nullable: true)]
    private ?string $actionType = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'performed_by', referencedColumnName: 'id_user', nullable: true, onDelete: 'SET NULL')]
    private ?User $performedBy = null;

    #[ORM\Column(name: 'timestamp', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $timestamp = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    // ─── Getters / Setters ───────────────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getActionType(): ?string { return $this->actionType; }
    public function setActionType(?string $actionType): static { $this->actionType = $actionType; return $this; }

    public function getPerformedBy(): ?User { return $this->performedBy; }
    public function setPerformedBy(?User $performedBy): static { $this->performedBy = $performedBy; return $this; }

    public function getTimestamp(): ?\DateTimeInterface { return $this->timestamp; }
    public function setTimestamp(?\DateTimeInterface $timestamp): static { $this->timestamp = $timestamp; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    // ─── Legacy aliases for backward compat ──────────────────────────────────

    public function getAction(): ?string { return $this->actionType; }
    public function setAction(?string $action): static { $this->actionType = $action; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->timestamp; }
    public function setDate(?\DateTimeInterface $date): static { $this->timestamp = $date; return $this; }

    public function getStatut(): ?string { return $this->description; }
    public function setStatut(?string $statut): static { $this->description = $statut; return $this; }
}
