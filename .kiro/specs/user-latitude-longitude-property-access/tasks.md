# Implementation Tasks: User Entity Latitude/Longitude Properties

## Phase 1: Bug Exploration & Verification

- [x] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - PropertyAccessor Cannot Access Latitude/Longitude
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **GOAL**: Surface counterexamples that demonstrate the bug exists
  - **Test Implementation**: Write property-based test that attempts to access latitude/longitude properties on User entity using PropertyAccessor
  - Test Details:
    - Create User entity instance
    - Attempt to read 'latitude' property using PropertyAccessor::getValue()
    - Attempt to write 'latitude' property using PropertyAccessor::setValue()
    - Attempt to read 'longitude' property using PropertyAccessor::getValue()
    - Attempt to write 'longitude' property using PropertyAccessor::setValue()
    - Assert that all operations succeed without throwing NoSuchPropertyException
  - Expected Counterexamples on Unfixed Code:
    - PropertyAccessor throws `NoSuchPropertyException: "Can't get a way to read the property 'latitude' in class 'App\Entity\User'"`
    - PropertyAccessor throws `NoSuchPropertyException: "Can't get a way to read the property 'longitude' in class 'App\Entity\User'"`
  - Run test on UNFIXED code
  - **EXPECTED OUTCOME**: Test FAILS (this is correct - it proves the bug exists)
  - Document counterexamples found to understand root cause
  - Mark task complete when test is written, run, and failure is documented
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [x] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Existing User Functionality Without Location Data
  - **IMPORTANT**: Follow observation-first methodology
  - **GOAL**: Capture existing behavior that must be preserved after the fix
  - Test Implementation: Write property-based tests for User entity operations that do NOT involve latitude/longitude
  - Test Details:
    - Observe: User entity can be created without latitude/longitude data
    - Observe: All existing User properties (nom, prenom, email, password, cin, telephone, adresse, role) work correctly
    - Observe: User authentication and login flows function normally
    - Observe: User relationships (fermes, analyses, userLogs, userFaces) load and work correctly
    - Observe: Database queries for existing User records return all properties without errors
    - Write property-based tests capturing these observed behaviors:
      - Test user creation without location data succeeds
      - Test user authentication with various credentials works
      - Test user property updates (nom, prenom, email, etc.) work correctly
      - Test user relationships load and function correctly
      - Test database queries return expected results
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2, 3.3, 3.4_

## Phase 2: Implementation

- [-] 3. Add latitude/longitude properties to User entity

  - [ ] 3.1 Add ORM column mappings and private properties
    - Open `src/Entity/User.php`
    - Add Doctrine ORM column attributes for latitude and longitude:
      - `#[ORM\Column(type: "float", nullable: true)]` above latitude property
      - `#[ORM\Column(type: "float", nullable: true)]` above longitude property
    - Add private properties:
      - `private ?float $latitude = null;`
      - `private ?float $longitude = null;`
    - Location: Add after existing properties (around line 75, after imageUrl property)
    - Pattern: Follow exact same pattern as Ferme entity
    - _Bug_Condition: isBugCondition(input) where input attempts to access latitude/longitude properties_
    - _Expected_Behavior: User entity has properly defined ORM mappings for latitude/longitude_
    - _Preservation: All existing User properties and relationships remain unchanged_
    - _Requirements: 2.1, 2.2, 2.3, 2.4_

  - [ ] 3.2 Add getter methods
    - Add `getLatitude()` method:
      - Signature: `public function getLatitude(): ?float`
      - Implementation: `return $this->latitude;`
    - Add `getLongitude()` method:
      - Signature: `public function getLongitude(): ?float`
      - Implementation: `return $this->longitude;`
    - Return nullable float to allow null values
    - Ensure PropertyAccessor can use these methods to read properties
    - _Bug_Condition: isBugCondition(input) where input attempts to read latitude/longitude_
    - _Expected_Behavior: Getter methods allow PropertyAccessor to read properties without exceptions_
    - _Requirements: 2.3, 2.4_

  - [ ] 3.3 Add setter methods
    - Add `setLatitude()` method:
      - Signature: `public function setLatitude(?float $latitude): static`
      - Implementation: `$this->latitude = $latitude; return $this;`
    - Add `setLongitude()` method:
      - Signature: `public function setLongitude(?float $longitude): static`
      - Implementation: `$this->longitude = $longitude; return $this;`
    - Use fluent interface (return $this) to match existing User entity pattern
    - Accept nullable float to allow clearing values
    - Ensure UserService.hydrate() can call these methods without errors
    - _Bug_Condition: isBugCondition(input) where input attempts to write latitude/longitude_
    - _Expected_Behavior: Setter methods allow form binding and UserService to set properties without exceptions_
    - _Requirements: 2.1, 2.2_

  - [ ] 3.4 Verify User entity changes compile and have no syntax errors
    - Run PHP syntax check on User.php
    - Verify no type errors or missing imports
    - Ensure all methods are properly formatted
    - _Requirements: 2.1, 2.2, 2.3, 2.4_

- [-] 4. Create and apply Doctrine migration

  - [ ] 4.1 Generate Doctrine migration
    - Run `php bin/console make:migration` to generate migration file
    - Migration will add `latitude` and `longitude` columns to user table
    - Verify migration file is created in `migrations/` directory
    - Review migration file to ensure:
      - Columns are FLOAT type
      - Columns are nullable
      - Column names match property names (latitude, longitude)
    - _Requirements: 2.1, 2.2, 2.3, 2.4_

  - [ ] 4.2 Apply migration to database
    - Run `php bin/console doctrine:migrations:migrate` to apply migration
    - Verify migration executes successfully
    - Verify user table now has latitude and longitude columns
    - Verify no data loss for existing records (columns are nullable)
    - _Requirements: 2.1, 2.2, 2.3, 2.4_

  - [ ] 4.3 Verify database schema
    - Query database to verify latitude and longitude columns exist
    - Verify columns are FLOAT type and nullable
    - Verify no impact on existing columns or data
    - _Requirements: 2.1, 2.2, 2.3, 2.4_

## Phase 3: Fix Verification

- [-] 5. Verify bug condition exploration test now passes

  - [ ] 5.1 Re-run bug condition exploration test from task 1
    - **Property 1: Expected Behavior** - PropertyAccessor Can Access Latitude/Longitude
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - The test from task 1 encodes the expected behavior
    - When this test passes, it confirms the expected behavior is satisfied
    - Run bug condition exploration test from step 1
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - Verify PropertyAccessor can read latitude/longitude without exceptions
    - Verify PropertyAccessor can write latitude/longitude without exceptions
    - Document that bug is fixed
    - _Requirements: 2.1, 2.2, 2.3, 2.4_

- [-] 6. Verify preservation tests still pass

  - [ ] 6.1 Re-run preservation property tests from task 2
    - **Property 2: Preservation** - Existing User Functionality Unchanged
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests from step 2
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Verify user creation without location data still works
    - Verify all existing User properties still work correctly
    - Verify user authentication and login flows still function
    - Verify user relationships still load and work correctly
    - Verify database queries still return expected results
    - Confirm all tests still pass after fix (no regressions)
    - _Requirements: 3.1, 3.2, 3.3, 3.4_

## Phase 4: Integration Testing

- [ ] 7. Test form binding integration

  - [ ] 7.1 Test RegistrationFormType with latitude/longitude fields
    - Create test that submits signup form with latitude/longitude data
    - Verify form data is successfully bound to User entity
    - Verify latitude and longitude values are correctly set on User object
    - Verify form validation passes with valid coordinates
    - Verify form validation fails with invalid coordinates (if validation rules exist)
    - _Requirements: 2.1, 2.2_

  - [ ] 7.2 Test form binding without latitude/longitude data
    - Create test that submits signup form without latitude/longitude data
    - Verify form submission succeeds
    - Verify latitude and longitude are null on User object
    - Verify existing form fields still work correctly
    - _Requirements: 3.1, 3.2_

- [ ] 8. Test UserService integration

  - [ ] 8.1 Test UserService.hydrate() with latitude/longitude
    - Create test that calls UserService.create() with latitude/longitude data
    - Verify UserService.hydrate() successfully calls setLatitude() and setLongitude()
    - Verify latitude and longitude values are correctly set on User entity
    - Verify user is created successfully in database
    - _Requirements: 2.1, 2.2_

  - [ ] 8.2 Test UserService.hydrate() without latitude/longitude
    - Create test that calls UserService.create() without latitude/longitude data
    - Verify UserService.hydrate() works correctly
    - Verify latitude and longitude are null on User entity
    - Verify user is created successfully in database
    - _Requirements: 3.1, 3.2_

  - [ ] 8.3 Test UserService.update() with latitude/longitude
    - Create test that calls UserService.update() with latitude/longitude data
    - Verify latitude and longitude values are correctly updated
    - Verify other user properties are not affected
    - Verify changes are persisted to database
    - _Requirements: 2.1, 2.2_

- [ ] 9. Test SecurityController integration

  - [ ] 9.1 Test SecurityController.signup() with latitude/longitude
    - Create test that submits signup form with latitude/longitude data
    - Verify SecurityController.signup() successfully calls getLatitude() and getLongitude()
    - Verify latitude and longitude values are correctly stored in session
    - Verify signup verification process completes successfully
    - _Requirements: 2.3, 2.4_

  - [ ] 9.2 Test SecurityController.signup() without latitude/longitude
    - Create test that submits signup form without latitude/longitude data
    - Verify SecurityController.signup() works correctly
    - Verify signup verification process completes successfully
    - Verify existing signup flow is unchanged
    - _Requirements: 3.1, 3.2_

- [ ] 10. Test PropertyAccessor integration

  - [ ] 10.1 Test PropertyAccessor read operations
    - Create test that uses PropertyAccessor to read latitude/longitude from User entity
    - Verify PropertyAccessor::getValue() returns correct values
    - Verify PropertyAccessor::getValue() returns null when properties are not set
    - Verify no NoSuchPropertyException is thrown
    - _Requirements: 2.3, 2.4_

  - [ ] 10.2 Test PropertyAccessor write operations
    - Create test that uses PropertyAccessor to write latitude/longitude to User entity
    - Verify PropertyAccessor::setValue() successfully sets values
    - Verify PropertyAccessor::setValue() can set null values
    - Verify no NoSuchPropertyException is thrown
    - _Requirements: 2.1, 2.2_

  - [ ] 10.3 Test PropertyAccessor with existing User properties
    - Create test that uses PropertyAccessor to read/write existing User properties
    - Verify PropertyAccessor still works correctly for all existing properties
    - Verify no side effects from new latitude/longitude properties
    - _Requirements: 3.1, 3.2, 3.3, 3.4_

## Phase 5: Comprehensive Testing

- [ ] 11. Write comprehensive unit tests

  - [ ] 11.1 Test User entity latitude/longitude getters and setters
    - Test getLatitude() and setLatitude() with various values (null, positive, negative, decimal)
    - Test getLongitude() and setLongitude() with various values
    - Test that setters return $this for fluent interface
    - Test that properties default to null
    - _Requirements: 2.1, 2.2, 2.3, 2.4_

  - [ ] 11.2 Test User entity initialization
    - Test User entity can be created without latitude/longitude
    - Test User entity can be created with latitude/longitude
    - Test that latitude/longitude are properly initialized
    - _Requirements: 2.1, 2.2, 3.1, 3.2_

  - [ ] 11.3 Test User entity relationships are unaffected
    - Test User entity relationships (fermes, analyses, userLogs, userFaces) still work
    - Test that adding latitude/longitude doesn't affect relationship loading
    - Test that relationship operations work correctly
    - _Requirements: 3.1, 3.2, 3.3, 3.4_

  - [ ] 11.4 Test User entity existing properties still work
    - Test all existing User properties (nom, prenom, email, password, cin, telephone, adresse, role)
    - Test that existing properties can be read and written correctly
    - Test that existing properties are not affected by new latitude/longitude properties
    - _Requirements: 3.1, 3.2, 3.3, 3.4_

- [ ] 12. Write property-based tests for comprehensive coverage

  - [ ] 12.1 Test latitude/longitude with random values
    - Generate random User entities with various latitude/longitude values
    - Verify getters return correct values
    - Verify setters correctly update values
    - Verify PropertyAccessor can read/write random values
    - _Requirements: 2.1, 2.2, 2.3, 2.4_

  - [ ] 12.2 Test User entity without latitude/longitude
    - Generate random User entities without latitude/longitude
    - Verify latitude/longitude default to null
    - Verify all other properties work correctly
    - Verify no errors or side effects
    - _Requirements: 3.1, 3.2, 3.3, 3.4_

  - [ ] 12.3 Test form binding with random data
    - Generate random form submissions with various latitude/longitude values
    - Verify form binding works correctly
    - Verify values are correctly set on User entity
    - Verify form validation works as expected
    - _Requirements: 2.1, 2.2_

  - [ ] 12.4 Test UserService with random data
    - Generate random UserService.create() calls with various latitude/longitude values
    - Verify properties are correctly set
    - Verify users are created successfully
    - Verify database persistence works correctly
    - _Requirements: 2.1, 2.2_

- [ ] 13. Write integration tests for complete workflows

  - [ ] 13.1 Test complete signup flow with location data
    - Test user submits signup form with latitude/longitude
    - Test form binding works correctly
    - Test UserService.create() sets properties correctly
    - Test SecurityController.signup() accesses properties correctly
    - Test user is created successfully in database
    - Test all integration points work together
    - _Requirements: 2.1, 2.2, 2.3, 2.4_

  - [ ] 13.2 Test complete signup flow without location data
    - Test user submits signup form without latitude/longitude
    - Test form binding works correctly
    - Test UserService.create() handles null values correctly
    - Test SecurityController.signup() handles null values correctly
    - Test user is created successfully in database
    - Test existing signup flow is unchanged
    - _Requirements: 3.1, 3.2, 3.3, 3.4_

  - [ ] 13.3 Test user profile update with location data
    - Test user updates profile with latitude/longitude
    - Test UserService.update() sets properties correctly
    - Test PropertyAccessor can read updated values
    - Test changes are persisted to database
    - _Requirements: 2.1, 2.2_

  - [ ] 13.4 Test user authentication with location data
    - Test user authentication works with latitude/longitude properties
    - Test login flows are unaffected by new properties
    - Test user roles and permissions work correctly
    - Test session handling works correctly
    - _Requirements: 3.1, 3.2, 3.3, 3.4_

## Phase 6: Final Verification

- [-] 14. Run full test suite

  - [ ] 14.1 Run all unit tests
    - Execute all unit tests for User entity
    - Verify all tests pass
    - Verify no regressions in existing tests
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 3.1, 3.2, 3.3, 3.4_

  - [ ] 14.2 Run all integration tests
    - Execute all integration tests
    - Verify all tests pass
    - Verify form binding, UserService, SecurityController, PropertyAccessor all work
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 3.1, 3.2, 3.3, 3.4_

  - [ ] 14.3 Run full application test suite
    - Execute complete test suite for entire application
    - Verify no regressions in other parts of application
    - Verify all existing tests still pass
    - _Requirements: 3.1, 3.2, 3.3, 3.4_

- [x] 15. Checkpoint - Ensure all tests pass and bug is fixed
  - Verify bug condition exploration test passes (bug is fixed)
  - Verify preservation tests pass (no regressions)
  - Verify all integration tests pass (all integration points work)
  - Verify full test suite passes (no side effects)
  - Confirm latitude/longitude properties are accessible on User entity
  - Confirm all existing User functionality is unchanged
  - Ask the user if questions arise or if additional testing is needed

## Optional Enhancement Tasks

- [ ] 16. Add validation for latitude/longitude values (OPTIONAL)
  - Add Symfony validation constraints to latitude/longitude properties
  - Validate latitude is between -90 and 90
  - Validate longitude is between -180 and 180
  - Add validation error messages
  - Test validation works correctly
  - _Requirements: 2.1, 2.2_

- [ ] 17. Add database indexes for latitude/longitude (OPTIONAL)
  - Create migration to add indexes on latitude and longitude columns
  - Improve query performance for location-based searches
  - Test that indexes are created correctly
  - _Requirements: 2.1, 2.2_

- [ ] 18. Add location-based query methods to User repository (OPTIONAL)
  - Add method to find users within geographic radius
  - Add method to find users by approximate location
  - Test query methods work correctly
  - _Requirements: 2.1, 2.2_

- [ ] 19. Add API endpoints for location data (OPTIONAL)
  - Add API endpoint to get user location
  - Add API endpoint to update user location
  - Test API endpoints work correctly
  - _Requirements: 2.1, 2.2_

- [ ] 20. Add frontend support for location data (OPTIONAL)
  - Add JavaScript to capture user location from browser
  - Add form fields to display/edit location
  - Add map visualization for user location
  - Test frontend integration works correctly
  - _Requirements: 2.1, 2.2_
