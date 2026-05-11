# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Symfony 6.4 PHP application for farm management (FARM AI). It includes animal/plant management, ERP modules (purchasing, sales, inventory), AI diagnostics via Groq API, and role-based access control.

## Common Commands

```bash
# Development server
symfony server:start

# Clear cache (after config/entity changes)
php bin/console cache:clear

# Run database migrations
php bin/console doctrine:migrations:migrate

# Create migration from entity changes
php bin/console doctrine:migrations:diff

# Run tests
php bin/phpunit                          # all tests
php bin/phpunit tests/Unit/Entity/        # unit tests only
php bin/phpunit --filter=PlanteController  # specific test

# Static analysis
vendor/bin/phpstan analyse src tests --level=5

# Code styling (if installed)
vendor/bin/php-cs-fixer fix src
```

## Architecture

### Entity Layer (`src/Entity/`)
Core domain models with Doctrine ORM attributes:
- `User.php` - authentication with roles (ROLE_ADMIN, ROLE_EXPERT, ROLE_AGRICOLE, ROLE_FOURNISSEUR)
- `Animal.php`, `Plante.php` - farm resources with health tracking
- `Analyse.php` - AI-powered diagnostic results from plant/animal images
- `Conseil.php` - recommendations generated from analyses
- `Ferme.php` - farm unit that groups animals/plants
- `Arrosage.php` - watering schedules

### Controller Layer (`src/Controller/`)
- `Admin/` - admin-only functionality (dashboard, statistics, audit)
- `Api/` - REST API endpoints (AuthController, PlanteApiController, ApiSanteController)
- `ERP/` - enterprise module (Achat, Vente, Matiere, Produit, ServiceERP)
- `Web/` - public-facing pages (SecurityController, HomeController, FaceAuthController)
- Root controllers: AnimalController, PlanteController, AnalyseController, ConseilController

### Service Layer (`src/Service/`)
- `GroqService.php` - AI vision for plant/animal image analysis
- `PlantService.php`, `PerenualService.php` - plant database lookups
- `ERP/` - business logic for purchases (AchatService), sales (VenteService), inventory (StockService)
- `FarmPredictor.php` - farm yield predictions

### Security (`config/packages/security.yaml`)
- Two firewall zones: `api` (stateless) for REST endpoints, `main` for web
- Role hierarchy: ROLE_ADMIN > ROLE_EXPERT > ROLE_AGRICOLE > ROLE_FOURNISSEUR
- Custom LoginSuccessHandler in `src/Security/`

## External Integrations

Environment variables needed:
- `GROQ_API_KEY` - AI vision analysis (used by `src/Service/GroqService.php`)
- `OPENWEATHER_API_KEY` - weather data
- `PERENUAL_API_KEY` - plant species database
- `TREFLE_API_TOKEN` - botanical API

## Database

- Primary: MariaDB 10.4 (`farmai` database)
- Tests: SQLite with `_test` suffix
- Doctrine timezone: UTC (`config/packages/doctrine.yaml`)
- Migrations in `migrations/` directory

## Dual App Architecture

Two separate applications share the `farmai` database. This app and another app (farmai_new) use the same database schema but have different entry points.