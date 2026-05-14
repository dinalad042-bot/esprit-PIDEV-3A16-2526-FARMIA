<?php

namespace App\Security;

use Symfony\Component\PasswordHasher\Hasher\CheckPasswordLength;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class BcryptPasswordHasher implements PasswordHasherInterface
{
    private int $cost;

    public function __construct(int $cost = 10)
    {
        $this->cost = $cost;
    }

    public function hash(string $plainPassword): string
    {
        $hash = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => $this->cost]);
        return $hash;
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        // Normalize: $2a$ and $2y$ are the same algorithm, PHP uses $2y$
        // password_verify handles both formats natively
        return password_verify($plainPassword, $hashedPassword);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return password_needs_rehash($hashedPassword, PASSWORD_BCRYPT, ['cost' => $this->cost]);
    }
}