<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for incoming chatbot requests.
 * Validates and structures the user message before it reaches the service layer.
 */
class ChatRequestDTO
{
    #[Assert\NotBlank(message: 'Le message ne peut pas être vide.')]
    #[Assert\Length(
        min: 1,
        max: 2000,
        maxMessage: 'Le message ne peut pas dépasser {{ limit }} caractères.'
    )]
    public readonly string $message;

    public function __construct(string $message)
    {
        $this->message = trim($message);
    }

    /**
     * Factory method to create a DTO from a raw request array.
     * Returns null if the message field is missing or empty.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): ?self
    {
        $message = $data['message'] ?? '';

        if (empty(trim($message))) {
            return null;
        }

        return new self($message);
    }
}
