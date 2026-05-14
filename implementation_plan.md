# Implementation Plan

## Overview
The **FARMIA** project is a Symfony 6.4 web application written in PHP 8.1+. It follows the classic MVC pattern:
* **Entities** (Doctrine ORM) model the domain objects such as `Animal`, `Plante`, `Ferme`, `Analyse`, `Conseil`, `User`.
* **Repositories** provide data‑access methods for each entity.
* **Controllers** (under `src/Controller/`) handle HTTP requests for the public web UI, an API, and an admin back-office. Controllers are grouped into `Web`, `Api`, and `Admin` namespaces.
* **Services** (e.g. `AuthService`, `GroqService`, `FaceEnrollmentService`) contain business logic reused across controllers.
* **Templates** (`templates/`) use Twig for rendering HTML pages.
* **Configuration** (`config/`, `.env*`) defines Symfony bundles, database connection, and environment variables.

The application also includes a set of **PHPUnit** tests (`tests/`) covering controllers, services and entities, as well as utility scripts for data generation and diagnostics.

## Types
* **PHP** – source code (`*.php`).
* **YAML** – Symfony configuration (`*.yaml`).
* **Twig** – view templates (`*.twig`).
* **SQL** – migration and seed files (`*.sql`).
* **Markdown** – documentation (`*.md`).
* **Env** – environment files (`.env*`).

## Files (selected highlights)
* `composer.json` – dependency definition.
* `src/Entity/` – Doctrine entities (`Animal.php`, `Plante.php`, `Ferme.php`, `Analyse.php`, `Conseil.php`, `User.php`).
* `src/Repository/` – repository classes for each entity.
* `src/Controller/` – all controller classes, grouped by purpose (Web, Api, Admin, etc.).
* `src/Service/` – business‑logic services (`AuthService.php`, `GroqService.php`, `FaceEnrollmentService.php`).
* `templates/` – Twig view files.
* `config/` – Symfony bundle and routing configuration.
* `tests/` – PHPUnit test suite.
* `public/` – web‑accessible assets and `index.php` front controller.

## Functions (representative examples)
* **Controller actions** – each public method annotated with `#[Route]` (e.g. `login()`, `newRequest()`, `exportAnalysePdf()`).
* **Service methods** – e.g. `AuthService::signup()`, `GroqService::diagnose()`, `FaceEnrollmentService::registerFace()`.
* **Utility scripts** – `gen_test.php`, `make_test.py`, `diagnose.php`.
* **Doctrine migrations** – generated under `migrations/`.

## Classes (key groups)
* **Entities** – `Animal`, `Plante`, `Ferme`, `Analyse`, `Conseil`, `User`, `UserFace`, `UserLog`.
* **Controllers** – `AnimalController`, `PlanteController`, `FermeController`, `AnalyseController`, `ConseilController`, `ChatbotController`, `SecurityController`, `ProfileController`, `DashboardController`, plus admin and API controllers.
* **Services** – `AuthService`, `UserService`, `UserLogService`, `GroqService`, `FaceEnrollmentService`, `CaptchaService`.
* **Repositories** – `AnimalRepository`, `PlanteRepository`, `FermeRepository`, `AnalyseRepository`, `ConseilRepository`, `UserRepository`.

## Dependencies (from `composer.json`)
* **Symfony components** – `framework-bundle`, `twig-bundle`, `security-bundle`, `mailer`, `validator`, `orm`, `asset`, `asset-mapper`, `http-client`, `process`, `runtime`, `ux-turbo`, etc.
* **Doctrine** – `doctrine/doctrine-bundle`, `doctrine/orm`, `doctrine/doctrine-migrations-bundle`.
* **PDF & reporting** – `dompdf/dompdf`, `knplabs/knp-snappy-bundle`.
* **Spreadsheet** – `phpoffice/phpspreadsheet`.
* **Testing** – `phpunit/phpunit`, `symfony/browser-kit`, `symfony/css-selector`, `symfony/phpunit-bridge`.
* **Other utilities** – `phpdocumentor/reflection-docblock`, `phpstan/phpdoc-parser`.

## Testing
* **PHPUnit** test suite located in `tests/` covering controllers, services and entities.
* **Functional tests** use Symfony's `WebTestCase` and `BrowserKit` to simulate HTTP requests.
* **Integration tests** for API endpoints (`src/Controller/Api/*`).
* **Data fixtures** can be loaded via the provided scripts (`gen_test.php`, `make_test.py`).

## Implementation Order
1. **Environment setup** – install PHP 8.1+, Composer, and run `composer install`.
2. **Database configuration** – copy `.env.example` to `.env`, set DB credentials, then run `php bin/console doctrine:database:create` and `php bin/console doctrine:migrations:migrate`.
3. **Run the test suite** – `php bin/phpunit` to ensure the baseline passes.
4. **Review routing** – inspect `config/routes/*.yaml` and controller annotations to understand request flow.
5. **Explore services** – read service classes in `src/Service/` to see business-logic dependencies.
6. **Documentation generation** – generate API docs if needed (`php bin/console api:doc:generate`).
7. **Iterative development** – follow the dependency chain (entities -> repositories -> services -> controllers) when adding features or fixing bugs.
8. **Deploy** – use Docker compose files (`compose.yaml`, `compose.override.yaml`) to spin up the application locally.

---

*This plan follows the deep‑planning protocol and provides a concise roadmap for understanding and extending the FARMIA Symfony project.*
