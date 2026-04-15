<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    // ─── Read ─────────────────────────────────────────────────────────────────

    public function findAll(): array
    {
        return $this->userRepository->findAllUsers();
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    /**
     * Create a new user from a data array.
     * The password must be provided in plain text; it will be hashed here.
     */
    public function create(array $data): User
    {
        $user = new User();
        $this->hydrate($user, $data);

        if (!empty($data['password'])) {
            $user->setPassword($this->hashPassword($user, $data['password']));
        }

        // Set timestamps
        $user->setCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    /**
     * Update an existing user.
     * If 'password' is present in $data it will be re-hashed.
     */
    public function update(User $user, array $data): User
    {
        $this->hydrate($user, $data);

        if (!empty($data['password'])) {
            $user->setPassword($this->hashPassword($user, $data['password']));
        }

        $user->setUpdatedAt(new \DateTime());

        $this->em->flush();

        return $user;
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    public function delete(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }

    // ─── Password helper ──────────────────────────────────────────────────────

    public function hashPassword(User $user, string $plainPassword): string
    {
        return $this->passwordHasher->hashPassword($user, $plainPassword);
    }

    // ─── Internal ─────────────────────────────────────────────────────────────

    private function hydrate(User $user, array $data): void
    {
        if (isset($data['nom']))       $user->setNom($data['nom']);
        if (isset($data['prenom']))    $user->setPrenom($data['prenom']);
        if (isset($data['email']))     $user->setEmail($data['email']);
        if (isset($data['telephone'])) $user->setTelephone($data['telephone']);
        if (isset($data['adresse']))   $user->setAdresse($data['adresse']);
        if (isset($data['cin']))       $user->setCin($data['cin']);
        if (isset($data['image_url'])) $user->setImageUrl($data['image_url']);

        // The DB stores raw role names (ADMIN, EXPERT, etc.) without the ROLE_ prefix
        if (isset($data['role'])) {
            $role = $data['role'];
            // Strip the ROLE_ prefix if present, since DB ENUM is ADMIN, EXPERT, etc.
            if (str_starts_with($role, 'ROLE_')) {
                $role = substr($role, 5);
            }
            $user->setRole($role);
        }
    }
}
