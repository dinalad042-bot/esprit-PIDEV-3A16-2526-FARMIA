# User Entity Latitude/Longitude Properties - Bugfix Design

## Overview

The User entity is missing `latitude` and `longitude` properties that are actively referenced in multiple parts of the codebase (RegistrationFormType, UserService.hydrate(), and SecurityController). This causes a `NoSuchPropertyException` when PropertyAccessor attempts to read or write these properties during user signup and profile operations.

The fix involves adding these properties to the User entity with proper Doctrine ORM mapping, getters/setters, and database migration. The implementation follows the same pattern already established in the Ferme entity, ensuring consistency across the codebase.

## Glossary

- **Bug_Condition (C)**: The condition that triggers the bug - when PropertyAccessor or form binding attempts to access latitude/longitude properties on the User entity
- **Property (P)**: The desired behavior when latitude/longitude are accessed - properties should be readable/writable without throwing exceptions
- **Preservation**: Existing User entity functionality (authentication, relationships, other properties) that must remain unchanged
- **PropertyAccessor**: Symfony's PropertyAccessor component that reads/writes object properties via getters/setters or direct property access
- **Doctrine ORM Mapping**: PHP attributes that define how entity properties map to database columns
- **Database Migration**: Doctrine migration that adds latitude and longitude columns to the user table

## Bug Details

### Bug Condition

The bug manifests when the signup form is submitted with latitude/longitude data, or when UserService.hydrate() attempts to set these properties, or when SecurityController tries to access them. The User entity lacks the `latitude` and `longitude` properties that are being referenced in the codebase.

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input of type PropertyAccessRequest or FormData
  OUTPUT: boolean
  
  RETURN (input.propertyName IN ['latitude', 'longitude']
          OR input.formFieldName IN ['latitude', 'longitude'])
         AND User entity does NOT have getter/setter for property
         AND User entity does NOT have ORM mapping for property
END FUNCTION
```

### Examples

**Example 1: Form Binding Failure**
- Input: User submits signup form with latitude=36.8065 and longitude=10.1686
- Current Behavior: PropertyAccessor throws `NoSuchPropertyException: "Can't get a way to read the property 'latitude' in class 'App\Entity\User'"`
- Expected Behavior: Form data is successfully bound to User entity properties

**Example 2: UserService.hydrate() Failure**
- Input: UserService.create() called with array containing `['latitude' => 36.8065, 'longitude' => 10.1686]`
- Current Behavior: Fatal error when calling `$user->setLatitude()` - method does not exist
- Expected Behavior: Properties are successfully set on User entity

**Example 3: SecurityController Access Failure**
- Input: SecurityController.signup() calls `$user->getLatitude()` to store in session
- Current Behavior: Fatal error - method does not exist on User entity
- Expected Behavior: Method returns the latitude value or null if not set

**Example 4: Non-Buggy Input (Preservation)**
- Input: User entity created without latitude/longitude data
- Current Behavior: User creation succeeds with all other properties working
- Expected Behavior: User creation continues to succeed with all other properties working (unchanged)

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- User entity can be created without latitude/longitude data
- All existing User properties (nom, prenom, email, password, cin, telephone, adresse, role, etc.) continue to work exactly as before
- User authentication and login flows remain unaffected
- User relationships (fermes, analyses, userLogs, userFaces) continue to function correctly
- Database queries for existing User records return all properties without errors
- User entity validation rules for existing properties remain unchanged

**Scope:**
All operations that do NOT involve latitude/longitude properties should be completely unaffected by this fix. This includes:
- User creation without location data
- User authentication and authorization
- User profile updates for non-location properties
- User relationships and collections
- Existing database queries and migrations

## Hypothesized Root Cause

Based on the bug description and code analysis, the root causes are:

1. **Missing ORM Property Mapping**: The User entity lacks Doctrine ORM column mappings for latitude and longitude, unlike the Ferme entity which has them properly defined

2. **Missing Getter/Setter Methods**: The User entity does not have `getLatitude()`, `setLatitude()`, `getLongitude()`, and `setLongitude()` methods that PropertyAccessor and form binding require

3. **Form Type References Non-Existent Properties**: RegistrationFormType adds 'latitude' and 'longitude' as HiddenType fields mapped to User entity, but the entity doesn't have these properties

4. **UserService.hydrate() References Non-Existent Methods**: The hydrate() method attempts to call `setLatitude()` and `setLongitude()` which don't exist on the User entity

5. **SecurityController Expects Properties**: SecurityController calls `getLatitude()` and `getLongitude()` to store location data in the signup session, but these methods don't exist

## Correctness Properties

Property 1: Bug Condition - Latitude/Longitude Property Access

_For any_ input where PropertyAccessor or form binding attempts to read or write latitude/longitude properties on a User entity (isBugCondition returns true), the fixed User entity SHALL have properly defined getter and setter methods that allow these properties to be accessed without throwing exceptions.

**Validates: Requirements 2.1, 2.2, 2.3, 2.4**

Property 2: Preservation - Existing User Functionality

_For any_ input that does NOT involve latitude/longitude properties (isBugCondition returns false), the fixed User entity SHALL produce exactly the same behavior as the original entity, preserving all existing functionality for user creation, authentication, relationships, and other properties.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4**

## Fix Implementation

### Changes Required

Assuming our root cause analysis is correct, the fix requires changes to the User entity to add latitude and longitude properties following the same pattern as the Ferme entity.

**File**: `src/Entity/User.php`

**Function**: User class

**Specific Changes**:

1. **Add ORM Column Mappings**: Add Doctrine ORM column attributes for latitude and longitude properties
   - Add `#[ORM\Column(type: "float", nullable: true)]` attribute above latitude property
   - Add `#[ORM\Column(type: "float", nullable: true)]` attribute above longitude property
   - Use nullable: true to allow users without location data
   - Use type: "float" to store decimal coordinates

2. **Add Private Properties**: Declare the private properties in the User class
   - `private ?float $latitude = null;`
   - `private ?float $longitude = null;`
   - Initialize to null to match Ferme entity pattern

3. **Add Getter Methods**: Implement getter methods for PropertyAccessor and form binding
   - `public function getLatitude(): ?float { return $this->latitude; }`
   - `public function getLongitude(): ?float { return $this->longitude; }`
   - Return nullable float to allow null values

4. **Add Setter Methods**: Implement setter methods for UserService.hydrate() and form binding
   - `public function setLatitude(?float $latitude): static { $this->latitude = $latitude; return $this; }`
   - `public function setLongitude(?float $longitude): static { $this->longitude = $longitude; return $this; }`
   - Use fluent interface (return $this) to match existing User entity pattern
   - Accept nullable float to allow clearing values

5. **Create Database Migration**: Generate and execute Doctrine migration
   - Run `php bin/console make:migration` to generate migration file
   - Migration will add `latitude` and `longitude` columns to user table
   - Columns will be FLOAT type and nullable
   - Run `php bin/console doctrine:migrations:migrate` to apply migration

6. **Verify Integration Points**: Ensure all integration points work correctly
   - RegistrationFormType can bind latitude/longitude from form to User entity
   - UserService.hydrate() can call setLatitude() and setLongitude() without errors
   - SecurityController can call getLatitude() and getLongitude() to access values
   - PropertyAccessor can read/write properties without exceptions

### Implementation Details

**Location in User.php**: Add properties after existing properties (around line 75, after imageUrl property)

**Pattern Consistency**: Follow the exact same pattern as Ferme entity:
- Ferme has: `#[ORM\Column(type: "float", nullable: true)]` for latitude/longitude
- Ferme has: `private ?float $latitude = null;` and `private ?float $longitude = null;`
- Ferme has: `public function getLatitude(): ?float { return $this->latitude; }`
- Ferme has: `public function setLatitude(?float $latitude): static { $this->latitude = $latitude; return $this; }`

**Database Impact**: 
- Two new nullable FLOAT columns added to user table
- No data loss for existing records (columns are nullable)
- No impact on existing queries (columns are optional)

## Testing Strategy

### Validation Approach

The testing strategy follows a two-phase approach: first, surface counterexamples that demonstrate the bug on unfixed code, then verify the fix works correctly and preserves existing behavior.

### Exploratory Bug Condition Checking

**Goal**: Surface counterexamples that demonstrate the bug BEFORE implementing the fix. Confirm or refute the root cause analysis. If we refute, we will need to re-hypothesize.

**Test Plan**: Write tests that attempt to access latitude/longitude properties on User entity using PropertyAccessor and form binding. Run these tests on the UNFIXED code to observe failures and understand the root cause.

**Test Cases**:

1. **PropertyAccessor Read Test**: Attempt to read latitude property from User object using PropertyAccessor (will fail on unfixed code)
   - Create User entity instance
   - Use PropertyAccessor to read 'latitude' property
   - Expected: NoSuchPropertyException thrown

2. **PropertyAccessor Write Test**: Attempt to write latitude property to User object using PropertyAccessor (will fail on unfixed code)
   - Create User entity instance
   - Use PropertyAccessor to write 'latitude' property with value 36.8065
   - Expected: NoSuchPropertyException thrown

3. **Form Binding Test**: Submit signup form with latitude/longitude data (will fail on unfixed code)
   - Create RegistrationFormType with User entity
   - Submit form data including latitude and longitude
   - Expected: Form binding fails or exception thrown

4. **UserService.hydrate() Test**: Call UserService.create() with latitude/longitude data (will fail on unfixed code)
   - Call UserService.create() with array containing latitude and longitude
   - Expected: Fatal error when calling setLatitude() method

5. **SecurityController Test**: Call SecurityController.signup() and access latitude/longitude (will fail on unfixed code)
   - Submit signup form
   - SecurityController attempts to call getLatitude() and getLongitude()
   - Expected: Fatal error - methods do not exist

**Expected Counterexamples**:
- PropertyAccessor throws NoSuchPropertyException when accessing latitude/longitude
- Form binding fails to map latitude/longitude fields to User entity
- UserService.hydrate() throws fatal error when calling non-existent setter methods
- SecurityController throws fatal error when calling non-existent getter methods

### Fix Checking

**Goal**: Verify that for all inputs where the bug condition holds, the fixed function produces the expected behavior.

**Pseudocode:**
```
FOR ALL input WHERE isBugCondition(input) DO
  result := User entity with latitude/longitude properties
  ASSERT result.getLatitude() returns value or null
  ASSERT result.setLatitude(value) succeeds
  ASSERT result.getLongitude() returns value or null
  ASSERT result.setLongitude(value) succeeds
  ASSERT PropertyAccessor can read latitude/longitude
  ASSERT PropertyAccessor can write latitude/longitude
  ASSERT Form binding works with latitude/longitude fields
  ASSERT UserService.hydrate() can set latitude/longitude
  ASSERT SecurityController can access latitude/longitude
END FOR
```

### Preservation Checking

**Goal**: Verify that for all inputs where the bug condition does NOT hold, the fixed function produces the same result as the original function.

**Pseudocode:**
```
FOR ALL input WHERE NOT isBugCondition(input) DO
  ASSERT User entity without latitude/longitude data behaves identically
  ASSERT All existing User properties work exactly as before
  ASSERT User authentication and authorization unchanged
  ASSERT User relationships (fermes, analyses, etc.) unchanged
  ASSERT Database queries for existing records unchanged
END FOR
```

**Testing Approach**: Property-based testing is recommended for preservation checking because:
- It generates many test cases automatically across the input domain
- It catches edge cases that manual unit tests might miss
- It provides strong guarantees that behavior is unchanged for all non-buggy inputs

**Test Plan**: Observe behavior on UNFIXED code first for user creation without location data, then write property-based tests capturing that behavior.

**Test Cases**:

1. **User Creation Without Location Data**: Verify user creation works without latitude/longitude
   - Create user without providing latitude/longitude
   - Verify user is created successfully
   - Verify all other properties are set correctly
   - Verify latitude/longitude are null

2. **User Authentication Preservation**: Verify authentication flows unchanged
   - Create user and authenticate
   - Verify login works exactly as before
   - Verify roles and permissions unchanged

3. **User Relationships Preservation**: Verify relationships with Ferme, Analyse, etc. unchanged
   - Create user with fermes and analyses
   - Verify relationships load correctly
   - Verify collection operations work as before

4. **Database Query Preservation**: Verify existing queries return same results
   - Query users from database
   - Verify all properties returned correctly
   - Verify no additional queries or performance impact

5. **User Property Updates Preservation**: Verify updating other properties unchanged
   - Update user nom, prenom, email, etc.
   - Verify updates work exactly as before
   - Verify no side effects from new properties

### Unit Tests

- Test User entity getLatitude() and setLatitude() methods with various values (null, positive, negative, decimal)
- Test User entity getLongitude() and setLongitude() methods with various values
- Test User entity initialization with latitude/longitude in constructor
- Test that latitude/longitude properties are nullable and default to null
- Test that existing User properties continue to work (nom, prenom, email, etc.)
- Test User entity relationships (fermes, analyses, userLogs, userFaces) are unaffected

### Property-Based Tests

- Generate random User entities with various latitude/longitude values and verify getters return correct values
- Generate random User entities without latitude/longitude and verify they default to null
- Generate random form submissions with latitude/longitude and verify form binding works
- Generate random UserService.create() calls with latitude/longitude and verify properties are set
- Generate random User entities and verify all existing properties work correctly regardless of latitude/longitude values
- Generate random database queries and verify results are unchanged

### Integration Tests

- Test complete signup flow with latitude/longitude data from form submission
- Test SecurityController.signup() stores latitude/longitude in session correctly
- Test UserService.create() with latitude/longitude data creates user successfully
- Test UserService.update() with latitude/longitude data updates user successfully
- Test PropertyAccessor can read/write latitude/longitude on User entity
- Test form binding with RegistrationFormType correctly maps latitude/longitude fields
- Test that existing signup flow without latitude/longitude continues to work
- Test that user authentication and login flows are unaffected by new properties
