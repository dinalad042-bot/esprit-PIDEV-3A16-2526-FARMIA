# Merge Conflicts Report

## Date: 2026-04-24

## Summary
The merge of `farmia/emen-module-ferme` into `dev_` resulted in **35 conflicted files** out of 67 total changed files. The conflicts are primarily due to:

1. **Add/add conflicts** (22 files): Both branches added files with the same name but different content (e.g., AnimalController.php, FermeController.php, etc.)
2. **Content conflicts** (13 files): Both branches modified the same files with different changes (e.g., .env, composer.json, services.yaml)

## Conflict Categories

### 1. Configuration Files (4 conflicts)
- `.env` - Different API keys and configuration
- `composer.json` - Different dependencies (knp-snappy vs nominatim-provider)
- `composer.lock` - Locked dependency versions differ
- `config/services.yaml` - Service definitions differ

### 2. Controllers (7 conflicts)
- `src/Controller/Admin/UserController.php` - Content conflict
- `src/Controller/AnimalController.php` - Add/add conflict (different implementations)
- `src/Controller/FermeController.php` - Add/add conflict
- `src/Controller/PlanteController.php` - Add/add conflict
- `src/Controller/Web/DashboardController.php` - Content conflict

### 3. Entities (4 conflicts)
- `src/Entity/Animal.php` - Add/add conflict
- `src/Entity/Ferme.php` - Add/add conflict
- `src/Entity/Plante.php` - Add/add conflict
- `src/Entity/User.php` - Content conflict

### 4. Forms (2 conflicts)
- `src/Form/AnimalType.php` - Add/add conflict
- `src/Form/PlanteType.php` - Add/add conflict

### 5. Repositories (3 conflicts)
- `src/Repository/AnimalRepository.php` - Add/add conflict
- `src/Repository/FermeRepository.php` - Add/add conflict
- `src/Repository/PlanteRepository.php` - Add/add conflict

### 6. Services (1 conflict)
- `src/Service/WeatherService.php` - Add/add conflict

### 7. Templates (16 conflicts)
- `templates/admin/dashboard/index.html.twig`
- `templates/admin/users/index.html.twig`
- `templates/admin/users/map.html.twig`
- `templates/animal/_delete_form.html.twig`
- `templates/animal/_form.html.twig`
- `templates/animal/edit.html.twig`
- `templates/animal/index.html.twig`
- `templates/animal/new.html.twig`
- `templates/animal/pdf.html.twig`
- `templates/animal/show.html.twig`
- `templates/exploitation/index.html.twig`
- `templates/ferme/_delete_form.html.twig`
- `templates/ferme/_form.html.twig`
- `templates/ferme/edit.html.twig`
- `templates/ferme/index.html.twig`
- `templates/ferme/new.html.twig`
- `templates/ferme/pdf.html.twig`
- `templates/ferme/show.html.twig`
- `templates/layouts/agricole.html.twig`
- `templates/plante/_delete_form.html.twig`
- `templates/plante/_form.html.twig`
- `templates/plante/edit.html.twig`
- `templates/plante/index.html.twig`
- `templates/plante/new.html.twig`
- `templates/plante/pdf.html.twig`
- `templates/plante/show.html.twig`
- `templates/portal/agricole/index.html.twig`

### 8. Other Files (1 conflict)
- `symfony.lock` - Symfony package versions differ

## Key Differences

### AnimalController.php
**Current (dev_):** Traditional CRUD with form-based approach
**Incoming (emen-module-ferme):** API-driven approach with:
- Groq AI integration for animal health consultation
- Carnet de santé (health record) functionality
- Map integration for finding nearby veterinarians
- Validation-based approach instead of forms

### .env
**Current:** Has Python API URL, OpenAI, and Groq configuration
**Incoming:** Has OpenWeatherMap, Trefle API, Perenual API, and different Groq key

### composer.json
**Current:** Includes knplabs/knp-snappy-bundle for PDF generation
**Incoming:** Includes geocoder-php/nominatim-provider for location services

## Resolution Strategy

### Recommended Approach: Accept Incoming Changes
Since the `emen-module-ferme` branch represents a more complete and integrated agricultural module, the recommended resolution is:

1. **For Add/add conflicts**: Accept the incoming version (emen-module-ferme) as it contains the complete implementation
2. **For content conflicts**: Merge the configurations to include both sets of features

### Specific Resolutions

#### .env - MERGE REQUIRED
Keep both sets of environment variables:
- Python API URL, OpenAI, Groq (from current)
- OpenWeatherMap, Trefle, Perenual (from incoming)
- Use the more complete Groq key

#### composer.json - MERGE REQUIRED
Include both dependencies:
- `knplabs/knp-snappy-bundle` (for PDF generation)
- `geocoder-php/nominatim-provider` (for location services)

#### AnimalController.php - ACCEPT INCOMING
The incoming version is more feature-complete with:
- AI consultation capabilities
- Health record tracking
- Better validation
- Map integration

#### Templates - ACCEPT INCOMING
The incoming templates are part of a complete module implementation

## Files Successfully Merged (No Conflicts)

### New Files Added (25 files)
- 15 migration files
- 3 API controllers (ApiSanteController, PlanteApiController)
- 2 entities (Arrosage, SuiviSante)
- 1 modified entity (UserLog)
- 3 repositories (AnimalSante, Arrosage, SuiviSante)
- 4 services (FarmPredictor, PerenualService, PlantService, WeatherService)
- 5 templates (carnet, consultation, details, suivi, prediction, weather)

## Next Steps

1. Resolve all conflicts by accepting incoming changes where appropriate
2. Merge .env configurations to include all API keys
3. Update composer.json to include both sets of dependencies
4. Run `composer install` to update dependencies
5. Run migrations: `php bin/console doctrine:migrations:migrate`
6. Clear cache: `php bin/console cache:clear`
7. Apply stashed changes: `git stash pop`
8. Run tests to verify nothing is broken
