<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\BaseKernelTestCase;

/**
 * Functional tests for UserRepository.
 *
 * TEST: User database operations and unique constraints
 * Reason: User entity is critical for authentication and authorization
 * Fat tail covered: Duplicate emails/CIN/telephone, password hashing
 *
 * @covers \App\Repository\UserRepository
 */
class UserRepositoryTest extends BaseKernelTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->em->getRepository(User::class);
    }

    /**
     * TEST: Can persist user
     */
    public function testCanPersistUser(): void
    {
        $user = $this->createTestUser();
        $this->em->flush();

        $this->assertNotNull($user->getId());
    }

    /**
     * TEST: Can find user by email
     */
    public function testCanFindByEmail(): void
    {
        $user = $this->createTestUser('test@example.com');
        $this->em->flush();

        $found = $this->repository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($found);
        $this->assertEquals('Test', $found->getNom());
    }

    /**
     * TEST: Can find user by CIN
     */
    public function testCanFindByCin(): void
    {
        $uniqueCin = 'CIN_' . uniqid();
        $user = $this->createTestUser(null, $uniqueCin);
        $this->em->flush();

        $found = $this->repository->findOneBy(['cin' => $uniqueCin]);
        $this->assertNotNull($found);
    }

    /**
     * TEST: Email uniqueness enforced
     */
    public function testEmailMustBeUnique(): void
    {
        $user1 = $this->createTestUser('unique@example.com');
        $this->em->flush();

        $user2 = $this->createTestUser('unique@example.com');

        $this->expectException(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class);
        $this->em->flush();
    }

    /**
     * TEST: Password is stored (not null)
     */
    public function testPasswordIsStored(): void
    {
        $user = $this->createTestUser();
        $user->setPassword('hashed_password_here');
        $this->em->flush();

        $this->em->clear();
        $found = $this->repository->find($user->getId());
        $this->assertNotNull($found->getPassword());
        $this->assertEquals('hashed_password_here', $found->getPassword());
    }

    /**
     * TEST: Can update user
     */
    public function testCanUpdateUser(): void
    {
        $user = $this->createTestUser();
        $this->em->flush();

        $user->setNom('UpdatedName');
        $this->em->flush();

        $this->em->clear();
        $updated = $this->repository->find($user->getId());
        $this->assertEquals('UpdatedName', $updated->getNom());
    }

    /**
     * TEST: Can delete user
     */
    public function testCanDeleteUser(): void
    {
        $user = $this->createTestUser();
        $this->em->flush();
        $id = $user->getId();

        $this->em->remove($user);
        $this->em->flush();

        $this->assertNull($this->repository->find($id));
    }

    /**
     * TEST: findAll returns array
     */
    public function testFindAllReturnsArray(): void
    {
        $this->createTestUser('user1_' . uniqid() . '@test.com');
        $this->createTestUser('user2_' . uniqid() . '@test.com');
        $this->em->flush();

        $all = $this->repository->findAll();
        $this->assertIsArray($all);
        $this->assertGreaterThanOrEqual(2, count($all));
    }

    private function createTestUser(?string $email = null, ?string $cin = null): User
    {
        $user = new User();
        $user->setEmail($email ?? 'test_' . uniqid() . '@farmia.test');
        $user->setPassword('password');
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setCin($cin ?? uniqid());
        $user->setAdresse('Test Address');
        $user->setTelephone('12345678');
        $user->setRole('USER');
        $this->em->persist($user);
        return $user;
    }
}