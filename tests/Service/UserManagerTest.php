<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserManager;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class UserManagerTest extends TestCase
{
    private UserManager $userManager;
    /** @var UserRepository&\PHPUnit\Framework\MockObject\MockObject */
    private $userRepositoryMock;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->userManager = new UserManager($this->userRepositoryMock);
    }

    private function createValidUser(): User
    {
        $user = new User();
        $user->setNom('Doe');
        $user->setPrenom('John');
        $user->setEmail('john.doe@example.com');
        $user->setCin('12345678');
        $user->setTelephone('98765432');
        return $user;
    }

    public function testValidUserCreate(): void
    {
        $user = $this->createValidUser();

        $this->userRepositoryMock->method('existsByEmail')->willReturn(false);
        $this->userRepositoryMock->method('existsByCin')->willReturn(false);
        $this->userRepositoryMock->method('existsByTelephone')->willReturn(false);

        $result = $this->userManager->validateForCreate($user, 'password123');
        
        $this->assertTrue($result);
        $this->assertSame('john.doe@example.com', $user->getEmail());
    }

    public function testMissingName(): void
    {
        $user = $this->createValidUser();
        $user->setNom('   '); // Les espaces vides ne doivent pas passer

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom est obligatoire.');

        $this->userManager->validateForCreate($user, 'password123');
    }

    public function testInvalidEmail(): void
    {
        $user = $this->createValidUser();
        $user->setEmail('invalid-email');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Veuillez saisir une adresse email valide.');

        $this->userManager->validateForCreate($user, 'password123');
    }

    public function testCinNot8Digits(): void
    {
        $user = $this->createValidUser();
        $user->setCin('12345');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Le CIN doit contenir exactement 8 chiffres.');

        $this->userManager->validateForCreate($user, 'password123');
    }

    public function testTelephoneNot8Digits(): void
    {
        $user = $this->createValidUser();
        $user->setTelephone('abcdefgh');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Le téléphone doit contenir exactement 8 chiffres.');

        $this->userManager->validateForCreate($user, 'password123');
    }

    public function testPasswordTooShort(): void
    {
        $user = $this->createValidUser();

        $this->userRepositoryMock->method('existsByEmail')->willReturn(false);
        $this->userRepositoryMock->method('existsByCin')->willReturn(false);
        $this->userRepositoryMock->method('existsByTelephone')->willReturn(false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Le mot de passe doit contenir au moins 6 caractères.');

        $this->userManager->validateForCreate($user, '12345');
    }

    public function testDuplicateEmail(): void
    {
        $user = $this->createValidUser();

        $this->userRepositoryMock->expects($this->once())
            ->method('existsByEmail')
            ->with('john.doe@example.com', null)
            ->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cet email existe déjà.');

        $this->userManager->validateForCreate($user, 'password123');
    }

    public function testDuplicateCin(): void
    {
        $user = $this->createValidUser();

        $this->userRepositoryMock->method('existsByEmail')->willReturn(false);
        
        $this->userRepositoryMock->expects($this->once())
            ->method('existsByCin')
            ->with('12345678', null)
            ->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ce CIN existe déjà.');

        $this->userManager->validateForCreate($user, 'password123');
    }

    public function testDuplicateTelephone(): void
    {
        $user = $this->createValidUser();

        $this->userRepositoryMock->method('existsByEmail')->willReturn(false);
        $this->userRepositoryMock->method('existsByCin')->willReturn(false);
        
        $this->userRepositoryMock->expects($this->once())
            ->method('existsByTelephone')
            ->with('98765432', null)
            ->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ce numéro de téléphone existe déjà.');

        $this->userManager->validateForCreate($user, 'password123');
    }

    public function testUpdateAllowsSameEmailCinTelephoneForSameUser(): void
    {
        $user = $this->createValidUser();
        // Simulation de l'ID pour un User existant grâce à la réflexion (car l'Entité n'a probablement pas de setId)
        $reflection = new ReflectionClass($user);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, 1);

        $this->userRepositoryMock->expects($this->once())
            ->method('existsByEmail')
            ->with('john.doe@example.com', 1)
            ->willReturn(false);

        $this->userRepositoryMock->expects($this->once())
            ->method('existsByCin')
            ->with('12345678', 1)
            ->willReturn(false);

        $this->userRepositoryMock->expects($this->once())
            ->method('existsByTelephone')
            ->with('98765432', 1)
            ->willReturn(false);

        $result = $this->userManager->validateForUpdate($user); // sans mot de passe
        $this->assertTrue($result);
    }

    public function testUpdateRejectsDuplicateEmailForOtherUser(): void
    {
        $user = $this->createValidUser();
        
        $reflection = new ReflectionClass($user);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, 1);

        // Cette méthode retourne true, ce qui veut dire qu'un AUTRE user (id != 1) possède cet email
        $this->userRepositoryMock->expects($this->once())
            ->method('existsByEmail')
            ->with('john.doe@example.com', 1)
            ->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cet email existe déjà.');

        $this->userManager->validateForUpdate($user);
    }
}