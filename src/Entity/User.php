<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[UniqueEntity(fields: ['email'], message: 'Cet email existe déjà.')]
#[UniqueEntity(fields: ['cin'], message: 'Ce CIN existe déjà.')]
#[UniqueEntity(fields: ['telephone'], message: 'Ce numéro de téléphone existe déjà.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_user', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private ?string $nom = null;

    #[ORM\Column(name: 'prenom', type: 'string', length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    private ?string $prenom = null;

    #[ORM\Column(name: 'email', type: 'string', length: 150, unique: true, nullable: false)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'Veuillez saisir une adresse email valide.')]
    private ?string $email = null;

    #[ORM\Column(name: 'password', type: 'string', length: 255, nullable: false)]
    private ?string $password = null;

    #[ORM\Column(name: 'cin', type: 'string', length: 20, unique: true, nullable: true)]
    #[Assert\NotBlank(message: 'Le CIN est obligatoire.')]
    #[Assert\Length(exactly: 8, exactMessage: 'Le CIN doit contenir exactement 8 caractères.')]
    private ?string $cin = null;

    #[ORM\Column(name: 'adresse', type: 'text', nullable: true)]
    #[Assert\NotBlank(message: 'L\'adresse est obligatoire.')]
    private ?string $adresse = null;

    #[ORM\Column(name: 'telephone', type: 'string', length: 20, nullable: true)]
    #[Assert\NotBlank(message: 'Le téléphone est obligatoire.')]
    #[Assert\Length(exactly: 8, exactMessage: 'Le téléphone doit contenir exactement 8 caractères.')]
    private ?string $telephone = null;

    #[ORM\Column(name: 'image_url', type: 'string', length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(name: 'role', type: 'string', length: 50, nullable: true)]
    private ?string $role = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserLog::class, cascade: ['persist'])]
    private Collection $userLogs;

    #[ORM\OneToMany(mappedBy: 'technicien', targetEntity: Analyse::class)]
    private Collection $analyses;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Ferme::class)]
    private Collection $fermes;

    public function __construct()
    {
        $this->userLogs = new ArrayCollection();
        $this->analyses = new ArrayCollection();
        $this->fermes   = new ArrayCollection();
    }

    // ─── UserInterface ────────────────────────────────────────────────────────

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $dbRole = $this->role;
        // The DB stores 'ADMIN', 'EXPERT', etc. — prefix with ROLE_ for Symfony
        if ($dbRole && !str_starts_with($dbRole, 'ROLE_')) {
            $dbRole = 'ROLE_' . $dbRole;
        }
        $r = $dbRole ?: 'ROLE_USER';
        return array_unique([$r, 'ROLE_USER']);
    }

    public function eraseCredentials(): void
    {
    }

    // ─── Getters / Setters ───────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }
    public function setNom(?string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }
    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getCin(): ?string
    {
        return $this->cin;
    }
    public function setCin(?string $cin): static
    {
        $this->cin = $cin;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }
    public function setRole(?string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }
    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }
    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }
    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
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

    public function getUserLogs(): Collection
    {
        return $this->userLogs;
    }

    public function getFermes(): Collection
    {
        return $this->fermes;
    }

    public function addFerme(Ferme $ferme): static
    {
        if (!$this->fermes->contains($ferme)) {
            $this->fermes->add($ferme);
            $ferme->setUser($this);
        }
        return $this;
    }

    public function removeFerme(Ferme $ferme): static
    {
        if ($this->fermes->removeElement($ferme)) {
            if ($ferme->getUser() === $this) {
                $ferme->setUser(null);
            }
        }
        return $this;
    }
}
