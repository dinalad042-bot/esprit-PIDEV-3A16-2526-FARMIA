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
