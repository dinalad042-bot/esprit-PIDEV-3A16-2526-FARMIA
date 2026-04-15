# Implementation Plan

[Overview]
Fix critical DQL field name mismatches causing 500 errors and resolve raw Twig template display issues.

The agricole portal is experiencing two critical issues: (1) Doctrine DQL queries in PlanteRepository and AnimalRepository reference `f.idFerme` which doesn't exist as a property in the Ferme entity (the property is `$id`), causing Semantical Errors when accessing analysis request pages. (2) Multiple portal pages (/ferme/, /plante/, /animal/) are displaying raw Twig code instead of rendered HTML, indicating a Symfony cache corruption or stale cache issue. This plan addresses both issues to restore full functionality to the agricole workflow.

[Types]
No type system changes required.

No new types, interfaces, or enums needed. The Ferme entity already has `getId()` and `getIdFerme()` methods for compatibility. The fix only involves correcting DQL syntax to reference the correct property name.

[Files]
Two repository files require modification to fix DQL field references.

Detailed breakdown:
- **Modify:** `src/Repository/PlanteRepository.php`
  - Line 35: Change `->andWhere('f.idFerme = :fermeId')` to `->andWhere('f.id = :fermeId')`
  - This fixes the `findByFerme()` method DQL query
  
- **Modify:** `src/Repository/AnimalRepository.php`
  - Line 63: Change `->andWhere('f.idFerme = :fermeId')` to `->andWhere('f.id = :fermeId')`
  - This fixes the `findByFerme()` method DQL query

- **Cache Clear:** After code changes, execute `php bin/console cache:clear` to resolve raw Twig display

[Functions]
Two repository methods require DQL syntax correction.

Detailed breakdown:
- **Modify:** `PlanteRepository::findByFerme(int $fermeId): array`
  - Current DQL: `->andWhere('f.idFerme = :fermeId')`
  - Fixed DQL: `->andWhere('f.id = :fermeId')`
  - File: `src/Repository/PlanteRepository.php`
  - Purpose: Filter plants by farm ID using correct property reference

- **Modify:** `AnimalRepository::findByFerme(int $fermeId): array`
  - Current DQL: `->andWhere('f.idFerme = :fermeId')`
  - Fixed DQL: `->andWhere('f.id = :fermeId')`
  - File: `src/Repository/AnimalRepository.php`
  - Purpose: Filter animals by farm ID using correct property reference

[Classes]
No class modifications required.

The Ferme entity class is already correctly defined with both `getId()` and `getIdFerme()` methods for backward compatibility. No changes to class structure, inheritance, or methods needed.

[Dependencies]
No new dependencies or version changes.

This fix uses existing Doctrine ORM functionality. No composer updates required.

[Testing]
Manual verification of fixed endpoints.

Test plan:
1. Access `/agricole/nouvelle-demande` - should no longer show 500 error
2. Access `/ferme/` - should render HTML, not raw Twig code
3. Access `/plante/` - should render HTML, not raw Twig code  
4. Access `/animal/` - should render HTML, not raw Twig code
5. Test farm creation, plant addition, and animal addition workflows

[Implementation Order]
Fix code first, then clear cache.

1. Fix `PlanteRepository.php` - change `f.idFerme` to `f.id` in findByFerme()
2. Fix `AnimalRepository.php` - change `f.idFerme` to `f.id` in findByFerme()
3. Clear Symfony cache with `php bin/console cache:clear`
4. Verify all pages load correctly without raw Twig code
5. Test analysis request functionality
