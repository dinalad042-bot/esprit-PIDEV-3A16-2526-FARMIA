<?php

namespace App\Security;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class LegacyPasswordHasher implements PasswordHasherInterface
{
    /**
     * Hash the password using a 16-character hex salt + SHA-256.
     * Compatible with the requested Java format: salt$hash
     */
    public function hash(string $plainPassword): string
    {
        // Generate an 8 byte salt, which gives 16 hex characters
        $salt = bin2hex(random_bytes(8));
        
        // Calculate hash: sha256 of salt concatenated with the plain password
        // This reproduces the logic described by the user.
        $hash = hash('sha256', $salt . $plainPassword);

        return $salt . '$' . $hash;
    }

    /**
     * Verifies the hashed password against the plain text password.
     */
    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        if (!str_contains($hashedPassword, '$')) {
            // Support legacy fallback or fail gracefully if format is strictly salt$hash
            return false;
        }

        // Split the stored value by '$'
        $parts = explode('$', $hashedPassword, 2);
        if (count($parts) !== 2) {
            return false;
        }

        $salt = $parts[0];
        $storedHash = $parts[1];

        // Recalculate hash with the extracted salt
        $computedHash = hash('sha256', $salt . $plainPassword);

        // Alternatively, if Java used password+salt, check that as well
        $computedHashAlt = hash('sha256', $plainPassword . $salt);

        return hash_equals($storedHash, $computedHash) || hash_equals($storedHash, $computedHashAlt);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return false;
    }
}
