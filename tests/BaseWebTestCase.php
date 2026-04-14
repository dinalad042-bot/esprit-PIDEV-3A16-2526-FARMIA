<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Base class for all controller functional tests.
 * Provides authentication helpers and database management.
 */
abstract class BaseWebTestCase extends WebTestCase
{
    protected static ?KernelBrowser $client = null;
    protected static ?EntityManagerInterface $em = null;
    protected static ?UserPasswordHasherInterface $passwordHasher = null;

    /**
     * Set up before each test.
     * Creates client and entity manager.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Always create a fresh client to avoid database locking issues
        self::$client = static::createClient();
        self::$em = static::getContainer()->get(EntityManagerInterface::class);
        self::$passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        
        // Create schema for in-memory database for each test
        $this->createSchema();
    }
    
    /**
     * Create database schema
     */
    private function createSchema(): void
    {
        $schemaTool = new SchemaTool(self::$em);
        $metadata = self::$em->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);
    }
    
    /**
     * Tear down after each test.
     */
    protected function tearDown(): void
    {
        // Clear entity manager to prevent memory leaks
        if (self::$em !== null && self::$em->isOpen()) {
            self::$em->clear();
        }
        
        parent::tearDown();
    }
    
    /**
     * Create a test user with specified role.
     * 
     * TEST: User Creation Helper
     * Reason: All controller tests need authenticated users
     * Fat tail: Duplicate emails cause unique constraint violations
     * 
     * @param string $role One of: ROLE_USER, ROLE_ADMIN, ROLE_EXPERT, ROLE_AGRICULTEUR, ROLE_FOURNISSEUR
     * @param string $email Unique email for the test user
     * @return User The created user entity
     */
    protected function createTestUser(string $role = 'ROLE_USER', string $email = null): User
    {
        $user = new User();
        $user->setEmail($email ?? 'test_' . uniqid() . '@farmia.test');
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setCin(uniqid());
        $user->setAdresse('123 Test Street');
        $user->setTelephone('12345678');
        $user->setRole(str_replace('ROLE_', '', $role));
        $user->setCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());
        
        // Hash password
        $hashedPassword = self::$passwordHasher->hashPassword($user, 'testpassword123');
        $user->setPassword($hashedPassword);
        
        self::$em->persist($user);
        self::$em->flush();
        
        return $user;
    }
    
    /**
     * Log in as a specific user.
     * 
     * TEST: Authentication Simulation
     * Reason: Controller tests need authenticated sessions
     * Fat tail: Session not persisted between requests
     * 
     * @param User $user The user to log in as
     */
    protected function loginAs(User $user): void
    {
        self::$client->loginUser($user);
    }
    
    /**
     * Log in with a specific role (creates user automatically).
     * 
     * @param string $role The role to log in as
     * @return User The created and logged-in user
     */
    protected function loginWithRole(string $role): User
    {
        $user = $this->createTestUser($role);
        $this->loginAs($user);
        return $user;
    }

    /**
     * Log in as an expert user.
     * 
     * @return User The created and logged-in expert user
     */
    protected function loginAsExpert(): User
    {
        return $this->loginWithRole('ROLE_EXPERT');
    }

    /**
     * Log in as a regular user.
     * 
     * @return User The created and logged-in user
     */
    protected function loginAsUser(): User
    {
        return $this->loginWithRole('ROLE_USER');
    }

    /**
     * Log out the current user.
     */
    protected function logout(): void
    {
        self::$client->request('GET', '/logout');
    }
    
    /**
     * Assert that a flash message exists.
     * 
     * @param string $type The flash type (success, error, warning, etc.)
     * @param string|null $message Optional message content to check
     */
    protected function assertFlashMessageExists(string $type, ?string $message = null): void
    {
        $flashBag = self::$client->getContainer()->get('session.factory')->createSession()->getFlashBag();
        $messages = $flashBag->get($type);
        
        $this->assertNotEmpty($messages, "No flash message of type '$type' found");
        
        if ($message !== null) {
            $this->assertContains($message, $messages, "Flash message does not contain expected text");
        }
    }
    
    /**
     * Get the entity manager.
     * 
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return self::$em;
    }

    /**
     * Get the client browser.
     * 
     * @return KernelBrowser
     */
    protected function getBrowser(): KernelBrowser
    {
        return self::$client;
    }
}
