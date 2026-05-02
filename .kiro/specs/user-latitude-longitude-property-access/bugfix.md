# Bugfix Requirements Document: User Entity Missing Latitude/Longitude Properties

## Introduction

The application is throwing a `NoSuchPropertyException: "Can't get a way to read the property 'latitude' in class 'App\Entity\User'"` when attempting to access latitude and longitude properties on the User entity. This error occurs in `PropertyAccessor.php` during user signup verification and profile operations. The User entity is missing the `latitude` and `longitude` properties that are being referenced in multiple parts of the codebase (UserService, SecurityController, RegistrationFormType), causing PropertyAccessor to fail when trying to read or write these properties.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN the signup form is submitted with latitude/longitude data THEN the system throws `NoSuchPropertyException` because User entity lacks `latitude` property

1.2 WHEN UserService.hydrate() attempts to call `$user->setLatitude()` THEN the system throws a fatal error because the method does not exist on User entity

1.3 WHEN SecurityController tries to access `$user->getLatitude()` during signup verification THEN the system throws `NoSuchPropertyException` because the getter method is not defined

1.4 WHEN PropertyAccessor is used to read the 'latitude' property from a User object THEN the system cannot find a way to access the property and throws an exception

### Expected Behavior (Correct)

2.1 WHEN the signup form is submitted with latitude/longitude data THEN the system successfully stores these values on the User entity without throwing exceptions

2.2 WHEN UserService.hydrate() calls `$user->setLatitude()` THEN the system successfully sets the latitude value on the User entity

2.3 WHEN SecurityController accesses `$user->getLatitude()` during signup verification THEN the system returns the stored latitude value or null if not set

2.4 WHEN PropertyAccessor is used to read the 'latitude' property from a User object THEN the system successfully retrieves the value through the getter method

### Unchanged Behavior (Regression Prevention)

3.1 WHEN a User entity is created without latitude/longitude data THEN the system SHALL CONTINUE TO allow user creation with all other properties functioning normally

3.2 WHEN existing User records are queried from the database THEN the system SHALL CONTINUE TO return all other user properties (nom, prenom, email, etc.) without any changes

3.3 WHEN user authentication and login flows are executed THEN the system SHALL CONTINUE TO work as before without any impact from adding latitude/longitude properties

3.4 WHEN the Ferme entity uses latitude/longitude properties THEN the system SHALL CONTINUE TO function correctly as these properties already exist on Ferme entity
