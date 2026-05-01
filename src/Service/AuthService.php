<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\LegacyPasswordHasher;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserService $userService,
        private readonly LegacyPasswordHasher $passwordHasher,
    ) {}

    // ─── Signup ───────────────────────────────────────────────────────────────

    /**
     * Register a new user.
     * Returns the created User or throws \InvalidArgumentException on validation failure.
     *
     * @param array<string, mixed> $data
     */
    public function signup(array $data): User
    {
        if (empty($data['email']) || empty($data['password'])) {
            throw new \InvalidArgumentException('Email et mot de passe sont obligatoires.');
        }

        if ($this->userRepository->findByEmail($data['email'])) {
            throw new \InvalidArgumentException('L\'email ' . $data['email'] . ' est déjà utilisé.');
        }

        if (!empty($data['cin']) && $this->userRepository->findOneBy(['cin' => $data['cin']])) {
            throw new \InvalidArgumentException('Le CIN ' . $data['cin'] . ' est déjà utilisé par un autre compte.');
        }

        // Attribution du rôle par défaut si non fourni
        if (empty($data['role'])) {
            $data['role'] = 'ROLE_AGRICOLE';
        }

        // Validation stricte des rôles autorisés à l'inscription publique
        $allowedRoles = ['ROLE_AGRICOLE', 'ROLE_EXPERT', 'ROLE_FOURNISSEUR', 'AGRICOLE', 'EXPERT', 'FOURNISSEUR'];
        $roleToCheck = str_replace('ROLE_', '', $data['role']); // Pour uniformité avec la BD
        if (!in_array($roleToCheck, ['AGRICOLE', 'EXPERT', 'FOURNISSEUR'], true)) {
            throw new \InvalidArgumentException('Rôle invalide ou non autorisé pour l\'inscription.');
        }

        return $this->userService->create($data);
    }

    // ─── Login (API only) ────────────────────────────────────────────────────

    /**
     * Verify credentials and return the User on success, or null on failure.
     * Uses the LegacyPasswordHasher which handles the salt$hash format.
     */
    public function login(string $email, string $plainPassword): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return null;
        }

        $storedHash = $user->getPassword();

        if (!$storedHash || !$this->passwordHasher->verify($storedHash, $plainPassword)) {
            return null;
        }

        return $user;
    }
}
