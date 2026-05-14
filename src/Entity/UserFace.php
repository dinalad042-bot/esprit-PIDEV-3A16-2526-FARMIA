<?php

namespace App\Entity;

use App\Repository\UserFaceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserFaceRepository::class)]
#[ORM\Table(name: 'user_face')]
#[ORM\HasLifecycleCallbacks]
class UserFace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    /**
     * Relation vers l'utilisateur propriétaire de ce visage.
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userFaces')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id_user', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    /**
     * Chemin relatif vers le dossier des images dans python_api/dataset/<user_id>/.
     * Ex: "dataset/42/"
     */
    #[ORM\Column(name: 'image_path', type: 'string', length: 500, nullable: true)]
    private ?string $imagePath = null;

    /**
     * Nombre d'échantillons (images) enregistrés pour cet utilisateur.
     */
    #[ORM\Column(name: 'samples_count', type: 'integer', options: ['default' => 0])]
    private int $samplesCount = 0;

    /**
     * Indique si ce visage est actif pour la reconnaissance.
     * Permet de désactiver sans supprimer.
     */
    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    /**
     * Score de confiance moyen lors du dernier entraînement/test.
     * Plus il est bas, mieux c'est (LBPH).
     */
    #[ORM\Column(name: 'confidence_score', type: 'float', nullable: true)]
    private ?float $confidenceScore = null;

    /**
     * Date du premier enrôlement.
     */
    #[ORM\Column(name: 'enrolled_at', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $enrolledAt = null;

    /**
     * Date de la dernière mise à jour (nouvel enregistrement, réentraînement).
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    // ─── Lifecycle Callbacks ─────────────────────────────────────────────────

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTime();
        $this->enrolledAt = $now;
        $this->updatedAt  = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // ─── Getters / Setters ───────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getSamplesCount(): int
    {
        return $this->samplesCount;
    }

    public function setSamplesCount(int $samplesCount): static
    {
        $this->samplesCount = $samplesCount;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getConfidenceScore(): ?float
    {
        return $this->confidenceScore;
    }

    public function setConfidenceScore(?float $confidenceScore): static
    {
        $this->confidenceScore = $confidenceScore;
        return $this;
    }

    public function getEnrolledAt(): ?\DateTimeInterface
    {
        return $this->enrolledAt;
    }

    public function setEnrolledAt(\DateTimeInterface $enrolledAt): static
    {
        $this->enrolledAt = $enrolledAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
