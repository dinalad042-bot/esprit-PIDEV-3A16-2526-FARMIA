<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

/**
 * BUG CONDITION EXPLORATION TEST: PropertyAccessor Cannot Access Latitude/Longitude
 * 
 * **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
 * 
 * This test explores the bug condition where PropertyAccessor cannot access
 * latitude/longitude properties on the User entity. This test is EXPECTED TO FAIL
 * on unfixed code - failure confirms the bug exists.
 * 
 * CRITICAL: This test MUST FAIL on unfixed code - failure proves the bug exists.
 * DO NOT attempt to fix the test or the code when it fails.
 * GOAL: Surface counterexamples that demonstrate the bug exists.
 * 
 * Expected Counterexamples on Unfixed Code:
 * - PropertyAccessor throws NoSuchPropertyException when reading 'latitude'
 * - PropertyAccessor throws NoSuchPropertyException when writing 'latitude'
 * - PropertyAccessor throws NoSuchPropertyException when reading 'longitude'
 * - PropertyAccessor throws NoSuchPropertyException when writing 'longitude'
 */
class UserLatitudeLongitudePropertyAccessTest extends TestCase
{
    private PropertyAccessor $propertyAccessor;

    protected function setUp(): void
    {
        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * TEST: PropertyAccessor Can Read Latitude Property
     * 
     * Reason: PropertyAccessor must be able to read latitude from User entity
     * Fat tail: Form binding fails if PropertyAccessor cannot read latitude
     * 
     * Expected on unfixed code: NoSuchPropertyException
     * Expected on fixed code: Returns null or float value
     */
    public function testPropertyAccessorCanReadLatitude(): void
    {
        $user = new User();
        
        // This should NOT throw NoSuchPropertyException
        try {
            $latitude = $this->propertyAccessor->getValue($user, 'latitude');
            // If we get here, the property is accessible
            $this->assertNull($latitude);
        } catch (NoSuchPropertyException $e) {
            $this->fail("PropertyAccessor cannot read 'latitude' property: " . $e->getMessage());
        }
    }

    /**
     * TEST: PropertyAccessor Can Write Latitude Property
     * 
     * Reason: PropertyAccessor must be able to write latitude to User entity
     * Fat tail: Form binding fails if PropertyAccessor cannot write latitude
     * 
     * Expected on unfixed code: NoSuchPropertyException
     * Expected on fixed code: Successfully sets value
     */
    public function testPropertyAccessorCanWriteLatitude(): void
    {
        $user = new User();
        $testLatitude = 36.8065;
        
        // This should NOT throw NoSuchPropertyException
        try {
            $this->propertyAccessor->setValue($user, 'latitude', $testLatitude);
            // If we get here, the property is writable
            $this->assertEquals($testLatitude, $this->propertyAccessor->getValue($user, 'latitude'));
        } catch (NoSuchPropertyException $e) {
            $this->fail("PropertyAccessor cannot write 'latitude' property: " . $e->getMessage());
        }
    }

    /**
     * TEST: PropertyAccessor Can Read Longitude Property
     * 
     * Reason: PropertyAccessor must be able to read longitude from User entity
     * Fat tail: Form binding fails if PropertyAccessor cannot read longitude
     * 
     * Expected on unfixed code: NoSuchPropertyException
     * Expected on fixed code: Returns null or float value
     */
    public function testPropertyAccessorCanReadLongitude(): void
    {
        $user = new User();
        
        // This should NOT throw NoSuchPropertyException
        try {
            $longitude = $this->propertyAccessor->getValue($user, 'longitude');
            // If we get here, the property is accessible
            $this->assertNull($longitude);
        } catch (NoSuchPropertyException $e) {
            $this->fail("PropertyAccessor cannot read 'longitude' property: " . $e->getMessage());
        }
    }

    /**
     * TEST: PropertyAccessor Can Write Longitude Property
     * 
     * Reason: PropertyAccessor must be able to write longitude to User entity
     * Fat tail: Form binding fails if PropertyAccessor cannot write longitude
     * 
     * Expected on unfixed code: NoSuchPropertyException
     * Expected on fixed code: Successfully sets value
     */
    public function testPropertyAccessorCanWriteLongitude(): void
    {
        $user = new User();
        $testLongitude = 10.1686;
        
        // This should NOT throw NoSuchPropertyException
        try {
            $this->propertyAccessor->setValue($user, 'longitude', $testLongitude);
            // If we get here, the property is writable
            $this->assertEquals($testLongitude, $this->propertyAccessor->getValue($user, 'longitude'));
        } catch (NoSuchPropertyException $e) {
            $this->fail("PropertyAccessor cannot write 'longitude' property: " . $e->getMessage());
        }
    }

    /**
     * PROPERTY-BASED TEST: PropertyAccessor Works With Random Latitude/Longitude Values
     * 
     * **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
     * 
     * Reason: PropertyAccessor must work with various coordinate values
     * Fat tail: Form binding fails with certain coordinate values
     * 
     * This property-based test generates random latitude/longitude values
     * and verifies that PropertyAccessor can read and write them correctly.
     */
    public function testPropertyAccessorWithRandomCoordinates(): void
    {
        // Generate test cases with various coordinate values
        $testCases = [
            // Valid coordinates
            ['latitude' => 36.8065, 'longitude' => 10.1686],  // Tunisia
            ['latitude' => 48.8566, 'longitude' => 2.3522],   // Paris
            ['latitude' => -33.8688, 'longitude' => 151.2093], // Sydney
            ['latitude' => 0.0, 'longitude' => 0.0],           // Null Island
            ['latitude' => 90.0, 'longitude' => 180.0],        // North Pole, Date Line
            ['latitude' => -90.0, 'longitude' => -180.0],      // South Pole, Date Line
            ['latitude' => 45.5, 'longitude' => -122.5],       // Portland
            ['latitude' => null, 'longitude' => null],         // Null values
            ['latitude' => 0.0001, 'longitude' => 0.0001],     // Very small values
            ['latitude' => -0.0001, 'longitude' => -0.0001],   // Very small negative values
        ];

        foreach ($testCases as $coordinates) {
            $user = new User();
            $latitude = $coordinates['latitude'];
            $longitude = $coordinates['longitude'];

            // Test writing latitude
            try {
                $this->propertyAccessor->setValue($user, 'latitude', $latitude);
                $readLatitude = $this->propertyAccessor->getValue($user, 'latitude');
                $this->assertEquals($latitude, $readLatitude, 
                    "Latitude mismatch: expected $latitude, got $readLatitude");
            } catch (NoSuchPropertyException $e) {
                $this->fail("PropertyAccessor cannot access 'latitude' property with value $latitude: " . $e->getMessage());
            }

            // Test writing longitude
            try {
                $this->propertyAccessor->setValue($user, 'longitude', $longitude);
                $readLongitude = $this->propertyAccessor->getValue($user, 'longitude');
                $this->assertEquals($longitude, $readLongitude, 
                    "Longitude mismatch: expected $longitude, got $readLongitude");
            } catch (NoSuchPropertyException $e) {
                $this->fail("PropertyAccessor cannot access 'longitude' property with value $longitude: " . $e->getMessage());
            }
        }
    }

    /**
     * PROPERTY-BASED TEST: PropertyAccessor Works With Multiple User Instances
     * 
     * **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
     * 
     * Reason: PropertyAccessor must work consistently across multiple User instances
     * Fat tail: PropertyAccessor fails on certain User instances
     * 
     * This property-based test creates multiple User instances and verifies
     * that PropertyAccessor can read and write latitude/longitude on each one.
     */
    public function testPropertyAccessorWithMultipleUserInstances(): void
    {
        // Create multiple User instances
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $latitude = 36.8065 + ($i * 0.1);
            $longitude = 10.1686 + ($i * 0.1);

            try {
                // Write latitude and longitude
                $this->propertyAccessor->setValue($user, 'latitude', $latitude);
                $this->propertyAccessor->setValue($user, 'longitude', $longitude);

                // Read latitude and longitude
                $readLatitude = $this->propertyAccessor->getValue($user, 'latitude');
                $readLongitude = $this->propertyAccessor->getValue($user, 'longitude');

                // Verify values match
                $this->assertEquals($latitude, $readLatitude);
                $this->assertEquals($longitude, $readLongitude);
            } catch (NoSuchPropertyException $e) {
                $this->fail("PropertyAccessor failed on User instance $i: " . $e->getMessage());
            }
        }
    }

    /**
     * PROPERTY-BASED TEST: PropertyAccessor Works With Null Values
     * 
     * **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
     * 
     * Reason: PropertyAccessor must handle null values for optional coordinates
     * Fat tail: PropertyAccessor fails when setting null values
     * 
     * This property-based test verifies that PropertyAccessor can set and read
     * null values for latitude/longitude properties.
     */
    public function testPropertyAccessorWithNullValues(): void
    {
        $user = new User();

        // Set latitude and longitude to non-null values first
        try {
            $this->propertyAccessor->setValue($user, 'latitude', 36.8065);
            $this->propertyAccessor->setValue($user, 'longitude', 10.1686);
        } catch (NoSuchPropertyException $e) {
            $this->fail("PropertyAccessor cannot set initial values: " . $e->getMessage());
        }

        // Now set them to null
        try {
            $this->propertyAccessor->setValue($user, 'latitude', null);
            $this->propertyAccessor->setValue($user, 'longitude', null);

            // Verify they are null
            $latitude = $this->propertyAccessor->getValue($user, 'latitude');
            $longitude = $this->propertyAccessor->getValue($user, 'longitude');

            $this->assertNull($latitude);
            $this->assertNull($longitude);
        } catch (NoSuchPropertyException $e) {
            $this->fail("PropertyAccessor cannot handle null values: " . $e->getMessage());
        }
    }
}
