<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Ferme;
use App\Entity\Analyse;
use App\Entity\UserLog;
use App\Entity\UserFace;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * PRESERVATION PROPERTY TESTS: Existing User Functionality Without Location Data
 * 
 * **Validates: Requirements 3.1, 3.2, 3.3, 3.4**
 * 
 * This test suite captures existing User entity behavior that MUST be preserved
 * after adding latitude/longitude properties. These tests verify that:
 * 
 * - User entity can be created without latitude/longitude data
 * - All existing User properties (nom, prenom, email, password, cin, telephone, adresse, role) work correctly
 * - User authentication and login flows function normally
 * - User relationships (fermes, analyses, userLogs, userFaces) load and work correctly
 * - Database queries for existing User records return all properties without errors
 * 
 * IMPORTANT: Follow observation-first methodology
 * GOAL: Capture existing behavior that must be preserved after the fix
 * 
 * Run tests on UNFIXED code
 * EXPECTED OUTCOME: Tests PASS (this confirms baseline behavior to preserve)
 */
class UserPreservationPropertyTest extends TestCase
{
    /**
     * TEST: User Creation Without Location Data Succeeds
     * 
     * **Validates: Requirement 3.1**
     * 
     * Reason: User entity must be creatable without latitude/longitude data
     * Fat tail: User creation fails if latitude/longitude are required
     * 
     * This test verifies that a User can be created without providing
     * latitude/longitude values, and all other properties work correctly.
     */
    public function testUserCreationWithoutLocationDataSucceeds(): void
    {
        $user = new User();
        
        // Verify user is created successfully
        $this->assertInstanceOf(User::class, $user);
        
        // Verify user can be created without latitude/longitude
        $this->assertNull($user->getId());
        
        // Verify all other properties can be set
        $user->setNom('Dupont');
        $user->setPrenom('Jean');
        $user->setEmail('jean@example.com');
        $user->setPassword('hashed_password');
        $user->setCin('12345678');
        $user->setTelephone('98765432');
        $user->setAdresse('123 Rue de la Paix');
        $user->setRole('ROLE_USER');
        
        // Verify all properties are set correctly
        $this->assertEquals('Dupont', $user->getNom());
        $this->assertEquals('Jean', $user->getPrenom());
        $this->assertEquals('jean@example.com', $user->getEmail());
        $this->assertEquals('hashed_password', $user->getPassword());
        $this->assertEquals('12345678', $user->getCin());
        $this->assertEquals('98765432', $user->getTelephone());
        $this->assertEquals('123 Rue de la Paix', $user->getAdresse());
        $this->assertEquals('ROLE_USER', $user->getRole());
    }

    /**
     * PROPERTY-BASED TEST: User Properties Work With Various Values
     * 
     * **Validates: Requirement 3.1**
     * 
     * Reason: User properties must work with various input values
     * Fat tail: User properties fail with certain values
     * 
     * This property-based test generates various user property values
     * and verifies that all properties can be set and retrieved correctly.
     */
    public function testUserPropertiesWithVariousValues(): void
    {
        $testCases = [
            [
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'jean@example.com',
                'cin' => '12345678',
                'telephone' => '98765432',
                'adresse' => '123 Rue de la Paix',
                'role' => 'ROLE_USER',
            ],
            [
                'nom' => 'Martin',
                'prenom' => 'Marie',
                'email' => 'marie@example.com',
                'cin' => '87654321',
                'telephone' => '23456789',
                'adresse' => '456 Avenue des Champs',
                'role' => 'ROLE_ADMIN',
            ],
            [
                'nom' => 'Durand',
                'prenom' => 'Pierre',
                'email' => 'pierre@example.com',
                'cin' => '11111111',
                'telephone' => '11111111',
                'adresse' => '789 Boulevard Saint-Germain',
                'role' => 'ROLE_TECHNICIAN',
            ],
            [
                'nom' => null,
                'prenom' => null,
                'email' => 'test@example.com',
                'cin' => null,
                'telephone' => null,
                'adresse' => null,
                'role' => null,
            ],
        ];

        foreach ($testCases as $testCase) {
            $user = new User();
            
            // Set all properties
            $user->setNom($testCase['nom']);
            $user->setPrenom($testCase['prenom']);
            $user->setEmail($testCase['email']);
            $user->setPassword('hashed_password');
            $user->setCin($testCase['cin']);
            $user->setTelephone($testCase['telephone']);
            $user->setAdresse($testCase['adresse']);
            $user->setRole($testCase['role']);
            
            // Verify all properties are set correctly
            $this->assertEquals($testCase['nom'], $user->getNom());
            $this->assertEquals($testCase['prenom'], $user->getPrenom());
            $this->assertEquals($testCase['email'], $user->getEmail());
            $this->assertEquals('hashed_password', $user->getPassword());
            $this->assertEquals($testCase['cin'], $user->getCin());
            $this->assertEquals($testCase['telephone'], $user->getTelephone());
            $this->assertEquals($testCase['adresse'], $user->getAdresse());
            $this->assertEquals($testCase['role'], $user->getRole());
        }
    }

    /**
     * TEST: User Authentication Properties Work Correctly
     * 
     * **Validates: Requirement 3.2**
     * 
     * Reason: User authentication must function normally
     * Fat tail: Authentication fails if properties are broken
     * 
     * This test verifies that User authentication properties and methods
     * work correctly without latitude/longitude data.
     */
    public function testUserAuthenticationPropertiesWork(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('hashed_password');
        $user->setRole('ROLE_USER');
        
        // Verify getUserIdentifier returns email
        $this->assertEquals('test@example.com', $user->getUserIdentifier());
        
        // Verify getRoles includes ROLE_USER
        $roles = $user->getRoles();
        $this->assertContains('ROLE_USER', $roles);
        
        // Verify eraseCredentials doesn't throw exception
        $user->eraseCredentials();
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    /**
     * PROPERTY-BASED TEST: User Authentication With Various Credentials
     * 
     * **Validates: Requirement 3.2**
     * 
     * Reason: User authentication must work with various credential combinations
     * Fat tail: Authentication fails with certain credential values
     * 
     * This property-based test generates various credential combinations
     * and verifies that authentication properties work correctly.
     */
    public function testUserAuthenticationWithVariousCredentials(): void
    {
        $credentialCases = [
            ['email' => 'user1@example.com', 'role' => 'ROLE_USER'],
            ['email' => 'admin@example.com', 'role' => 'ROLE_ADMIN'],
            ['email' => 'tech@example.com', 'role' => 'ROLE_TECHNICIAN'],
            ['email' => 'test@example.com', 'role' => null],
            ['email' => 'user@example.com', 'role' => 'ROLE_FARMER'],
        ];

        foreach ($credentialCases as $credentials) {
            $user = new User();
            $user->setEmail($credentials['email']);
            $user->setPassword('hashed_password');
            $user->setRole($credentials['role']);
            
            // Verify getUserIdentifier returns email
            $this->assertEquals($credentials['email'], $user->getUserIdentifier());
            
            // Verify getRoles includes ROLE_USER
            $roles = $user->getRoles();
            $this->assertContains('ROLE_USER', $roles);
            
            // Verify role is included if set
            if ($credentials['role']) {
                $expectedRole = str_starts_with($credentials['role'], 'ROLE_') 
                    ? $credentials['role'] 
                    : 'ROLE_' . $credentials['role'];
                $this->assertContains($expectedRole, $roles);
            }
        }
    }

    /**
     * TEST: User Relationships Load and Work Correctly
     * 
     * **Validates: Requirement 3.3**
     * 
     * Reason: User relationships must function normally
     * Fat tail: Relationships break if collections are not initialized
     * 
     * This test verifies that all User relationships (fermes, analyses, userLogs, userFaces)
     * are properly initialized and can be used without errors.
     */
    public function testUserRelationshipsLoadCorrectly(): void
    {
        $user = new User();
        
        // Verify all collections are initialized
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $user->getFermes());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $user->getAnalyses());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $user->getUserLogs());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $user->getUserFaces());
        
        // Verify collections are empty initially
        $this->assertCount(0, $user->getFermes());
        $this->assertCount(0, $user->getAnalyses());
        $this->assertCount(0, $user->getUserLogs());
        $this->assertCount(0, $user->getUserFaces());
    }

    /**
     * PROPERTY-BASED TEST: User Relationships Work With Multiple Items
     * 
     * **Validates: Requirement 3.3**
     * 
     * Reason: User relationships must work with multiple related entities
     * Fat tail: Relationships fail with certain numbers of items
     * 
     * This property-based test adds multiple related entities to User
     * and verifies that relationships work correctly.
     */
    public function testUserRelationshipsWithMultipleItems(): void
    {
        $user = new User();
        
        // Add multiple fermes
        for ($i = 0; $i < 5; $i++) {
            $ferme = new Ferme();
            $ferme->setNomFerme("Ferme $i");
            $ferme->setLieu("Lieu $i");
            $ferme->setSurface(100.0 + $i);
            $user->addFerme($ferme);
        }
        
        // Verify fermes are added correctly
        $this->assertCount(5, $user->getFermes());
        
        // Verify each ferme has user set
        foreach ($user->getFermes() as $ferme) {
            $this->assertEquals($user, $ferme->getUser());
        }
    }

    /**
     * TEST: User Property Updates Work Correctly
     * 
     * **Validates: Requirement 3.1**
     * 
     * Reason: User properties must be updatable
     * Fat tail: Property updates fail or don't persist
     * 
     * This test verifies that User properties can be updated after initial creation.
     */
    public function testUserPropertyUpdatesWork(): void
    {
        $user = new User();
        
        // Set initial properties
        $user->setNom('Dupont');
        $user->setPrenom('Jean');
        $user->setEmail('jean@example.com');
        
        // Update properties
        $user->setNom('Martin');
        $user->setPrenom('Marie');
        $user->setEmail('marie@example.com');
        
        // Verify updates are applied
        $this->assertEquals('Martin', $user->getNom());
        $this->assertEquals('Marie', $user->getPrenom());
        $this->assertEquals('marie@example.com', $user->getEmail());
    }

    /**
     * PROPERTY-BASED TEST: User Property Updates With Various Values
     * 
     * **Validates: Requirement 3.1**
     * 
     * Reason: User properties must be updatable with various values
     * Fat tail: Property updates fail with certain values
     * 
     * This property-based test updates User properties multiple times
     * with various values and verifies that updates work correctly.
     */
    public function testUserPropertyUpdatesWithVariousValues(): void
    {
        $user = new User();
        
        // Initial values
        $user->setNom('Initial');
        $user->setEmail('initial@example.com');
        
        // Update values multiple times
        $updateValues = [
            ['nom' => 'Update1', 'email' => 'update1@example.com'],
            ['nom' => 'Update2', 'email' => 'update2@example.com'],
            ['nom' => 'Update3', 'email' => 'update3@example.com'],
            ['nom' => null, 'email' => 'null@example.com'],
        ];
        
        foreach ($updateValues as $values) {
            $user->setNom($values['nom']);
            $user->setEmail($values['email']);
            
            $this->assertEquals($values['nom'], $user->getNom());
            $this->assertEquals($values['email'], $user->getEmail());
        }
    }

    /**
     * TEST: User Timestamps Work Correctly
     * 
     * **Validates: Requirement 3.1**
     * 
     * Reason: User timestamps must be settable and retrievable
     * Fat tail: Timestamp operations fail
     * 
     * This test verifies that User createdAt and updatedAt timestamps work correctly.
     */
    public function testUserTimestampsWork(): void
    {
        $user = new User();
        
        $now = new \DateTime();
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);
        
        $this->assertEquals($now, $user->getCreatedAt());
        $this->assertEquals($now, $user->getUpdatedAt());
    }

    /**
     * TEST: User Image URL Works Correctly
     * 
     * **Validates: Requirement 3.1**
     * 
     * Reason: User image URL must be settable and retrievable
     * Fat tail: Image URL operations fail
     * 
     * This test verifies that User imageUrl property works correctly.
     */
    public function testUserImageUrlWorks(): void
    {
        $user = new User();
        
        $imageUrl = 'https://example.com/image.jpg';
        $user->setImageUrl($imageUrl);
        
        $this->assertEquals($imageUrl, $user->getImageUrl());
    }

    /**
     * PROPERTY-BASED TEST: User Facial Authentication Methods Work
     * 
     * **Validates: Requirement 3.3**
     * 
     * Reason: User facial authentication methods must work correctly
     * Fat tail: Facial auth methods fail or return incorrect values
     * 
     * This property-based test verifies that hasFaceAuth() and getActiveFace()
     * methods work correctly with various userFaces states.
     */
    public function testUserFacialAuthenticationMethods(): void
    {
        $user = new User();
        
        // Initially, no face auth
        $this->assertFalse($user->hasFaceAuth());
        $this->assertNull($user->getActiveFace());
        
        // Add a face
        $userFace = new UserFace();
        $user->getUserFaces()->add($userFace);
        
        // Now should have face auth
        $this->assertTrue($user->hasFaceAuth());
        $this->assertNotNull($user->getActiveFace());
    }

    /**
     * TEST: User Fluent Interface Works
     * 
     * **Validates: Requirement 3.1**
     * 
     * Reason: User setters must return $this for fluent interface
     * Fat tail: Fluent interface breaks if setters don't return $this
     * 
     * This test verifies that User setters support fluent interface chaining.
     */
    public function testUserFluentInterfaceWorks(): void
    {
        $user = (new User())
            ->setNom('Dupont')
            ->setPrenom('Jean')
            ->setEmail('jean@example.com')
            ->setPassword('hashed_password')
            ->setCin('12345678')
            ->setTelephone('98765432')
            ->setAdresse('123 Rue de la Paix')
            ->setRole('ROLE_USER');
        
        // Verify all properties are set
        $this->assertEquals('Dupont', $user->getNom());
        $this->assertEquals('Jean', $user->getPrenom());
        $this->assertEquals('jean@example.com', $user->getEmail());
        $this->assertEquals('hashed_password', $user->getPassword());
        $this->assertEquals('12345678', $user->getCin());
        $this->assertEquals('98765432', $user->getTelephone());
        $this->assertEquals('123 Rue de la Paix', $user->getAdresse());
        $this->assertEquals('ROLE_USER', $user->getRole());
    }

    /**
     * PROPERTY-BASED TEST: User Ferme Relationship Bidirectionality
     * 
     * **Validates: Requirement 3.3**
     * 
     * Reason: User-Ferme relationship must be bidirectional
     * Fat tail: Bidirectional relationship breaks if not properly maintained
     * 
     * This property-based test verifies that adding/removing fermes
     * maintains bidirectional relationship correctly.
     */
    public function testUserFermeRelationshipBidirectionality(): void
    {
        $user = new User();
        
        // Add multiple fermes
        for ($i = 0; $i < 3; $i++) {
            $ferme = new Ferme();
            $ferme->setNomFerme("Ferme $i");
            $ferme->setLieu("Lieu $i");
            $ferme->setSurface(100.0);
            
            $user->addFerme($ferme);
            
            // Verify bidirectional relationship
            $this->assertTrue($user->getFermes()->contains($ferme));
            $this->assertEquals($user, $ferme->getUser());
        }
        
        // Remove a ferme
        $fermeToRemove = $user->getFermes()->first();
        $user->removeFerme($fermeToRemove);
        
        // Verify removal is bidirectional
        $this->assertFalse($user->getFermes()->contains($fermeToRemove));
        $this->assertNull($fermeToRemove->getUser());
    }

    /**
     * TEST: User Constructor Initializes All Collections
     * 
     * **Validates: Requirement 3.3**
     * 
     * Reason: User constructor must initialize all collections
     * Fat tail: Collections are null if not initialized
     * 
     * This test verifies that User constructor properly initializes all collections.
     */
    public function testUserConstructorInitializesAllCollections(): void
    {
        $user = new User();
        
        // Verify all collections are initialized (not null)
        $this->assertNotNull($user->getUserLogs());
        $this->assertNotNull($user->getFermes());
        $this->assertNotNull($user->getAnalyses());
        $this->assertNotNull($user->getUserFaces());
        
        // Verify they are Collection instances
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $user->getUserLogs());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $user->getFermes());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $user->getAnalyses());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $user->getUserFaces());
    }

    /**
     * PROPERTY-BASED TEST: User Properties Remain Unchanged After Multiple Operations
     * 
     * **Validates: Requirement 3.1**
     * 
     * Reason: User properties must remain stable after various operations
     * Fat tail: Properties change unexpectedly after operations
     * 
     * This property-based test performs various operations on User
     * and verifies that properties remain unchanged.
     */
    public function testUserPropertiesRemainUnchangedAfterOperations(): void
    {
        $user = new User();
        
        // Set initial properties
        $user->setNom('Dupont');
        $user->setPrenom('Jean');
        $user->setEmail('jean@example.com');
        $user->setPassword('hashed_password');
        $user->setCin('12345678');
        $user->setTelephone('98765432');
        $user->setAdresse('123 Rue de la Paix');
        $user->setRole('ROLE_USER');
        
        // Perform various operations
        $ferme = new Ferme();
        $ferme->setNomFerme('Test Ferme');
        $ferme->setLieu('Test Lieu');
        $ferme->setSurface(100.0);
        $user->addFerme($ferme);
        
        $user->getUserFaces();
        $user->getAnalyses();
        $user->getUserLogs();
        
        // Verify properties are unchanged
        $this->assertEquals('Dupont', $user->getNom());
        $this->assertEquals('Jean', $user->getPrenom());
        $this->assertEquals('jean@example.com', $user->getEmail());
        $this->assertEquals('hashed_password', $user->getPassword());
        $this->assertEquals('12345678', $user->getCin());
        $this->assertEquals('98765432', $user->getTelephone());
        $this->assertEquals('123 Rue de la Paix', $user->getAdresse());
        $this->assertEquals('ROLE_USER', $user->getRole());
    }
}
