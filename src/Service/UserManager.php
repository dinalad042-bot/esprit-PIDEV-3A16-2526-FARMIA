<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use InvalidArgumentException;

class UserManager
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function validateForCreate(User $user, ?string $plainPassword = null): bool
    {
        $this->normalizeAndValidateUser($user, null);

        if ($plainPassword === null || strlen(trim($plainPassword)) < 6) {
            throw new InvalidArgumentException('Le mot de passe doit contenir au moins 6 caractères.');
        }

        return true;
    }

    public function validateForUpdate(User $user, ?string $plainPassword = null): bool
    {
        $this->normalizeAndValidateUser($user, $user->getId());

        if ($plainPassword !== null && trim($plainPassword) !== '') {
            if (strlen(trim($plainPassword)) < 6) {
                throw new InvalidArgumentException('Le mot de passe doit contenir au moins 6 caractères.');
            }
        }

        return true;
    }

    private function normalizeAndValidateUser(User $user, ?int $excludeId): void
    {
        // R1: Nom obligatoire
        $nom = $user->getNom() !== null ? trim($user->getNom()) : '';
        if (empty($nom)) {
            throw new InvalidArgumentException('Le nom est obligatoire.');
        }
        $user->setNom($nom);

        // R2: Prénom obligatoire
        $prenom = $user->getPrenom() !== null ? trim($user->getPrenom()) : '';
        if (empty($prenom)) {
            throw new InvalidArgumentException('Le prénom est obligatoire.');
        }
        $user->setPrenom($prenom);

        // R3: Email obligatoire et valide
        $email = $user->getEmail() !== null ? strtolower(trim($user->getEmail())) : '';
        if (empty($email)) {
            throw new InvalidArgumentException('L\'email est obligatoire.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Veuillez saisir une adresse email valide.');
        }
        $user->setEmail($email);

        // R4: CIN obligatoire et exactement 8 chiffres
        $cin = $user->getCin() !== null ? str_replace(' ', '', trim($user->getCin())) : '';
        if (empty($cin)) {
            throw new InvalidArgumentException('Le CIN est obligatoire.');
        }
        if (!preg_match('/^\d{8}$/', $cin)) {
            throw new InvalidArgumentException('Le CIN doit contenir exactement 8 chiffres.');
        }
        $user->setCin($cin);

        // R5: Téléphone obligatoire et exactement 8 chiffres
        $telephone = $user->getTelephone() !== null ? str_replace(' ', '', trim($user->getTelephone())) : '';
        if (empty($telephone)) {
            throw new InvalidArgumentException('Le téléphone est obligatoire.');
        }
        if (!preg_match('/^\d{8}$/', $telephone)) {
            throw new InvalidArgumentException('Le téléphone doit contenir exactement 8 chiffres.');
        }
        $user->setTelephone($telephone);

        // R6: Email unique
        if ($this->userRepository->existsByEmail($email, $excludeId)) {
            throw new InvalidArgumentException('Cet email existe déjà.');
        }

        // R7: CIN unique
        if ($this->userRepository->existsByCin($cin, $excludeId)) {
            throw new InvalidArgumentException('Ce CIN existe déjà.');
        }

        // R8: Téléphone unique
        if ($this->userRepository->existsByTelephone($telephone, $excludeId)) {
            throw new InvalidArgumentException('Ce numéro de téléphone existe déjà.');
        }
    }
}
