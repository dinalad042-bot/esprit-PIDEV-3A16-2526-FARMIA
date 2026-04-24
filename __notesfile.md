# Project Findings (auto‑generated)

| Confidence | Status | Finding |
|------------|--------|---------|
| 1.0 | NEW | Database configuration mismatch: Docker‑compose defines Postgres service but `.env` uses MySQL/MariaDB (`DATABASE_URL="mysql://root@127.0.0.1:3306/farmia...`). |
| 0.9 | NEW | Duplicate method `index` found in multiple Controller classes (possible duplicate logic). |
| 0.9 | NEW | Potential undefined variable `$em` in `src/Controller/AnimalController.php`. |
| 0.9 | NEW | Broken include: `templates/partials/header.html.twig` referenced but file/directory missing. |
| 0.85 | NEW | Missing CSRF token check in `AnimalController::generatePdf` (GET route triggers PDF generation without token verification). |
| 0.85 | NEW | In `ConseilController`, query builder uses field `prioriteRaw` – likely typo (entity field is `priorite`). |
| 0.8 | NEW | Twig variable `animal_edit` used in `templates/animal/index.html.twig` without guaranteed controller assignment (may be undefined). |
| 0.8 | NEW | Duplicate query logic: `findBySearchAndSort` pattern duplicated across `AnimalRepository`, `PlanteRepository`, `FermeRepository`. |
| 0.8 | NEW | Duplicate PDF generation logic in `AnimalController`, `PlanteController`, `FermeController`, and `Admin/UserController` (identical Dompdf setup). |
| 0.8 | NEW | Duplicate Twig block for “alertes santé”/“alertes stock” – same statistical card logic repeated in `animal/index.html.twig` and `plante/index.html.twig`. |
| 0.8 | NEW | Unused dependency `EntityManagerInterface $em` in `AnalyseController` constructor (injected but never used). |
| 0.75 | NEW | Route naming conflict: `app_animal_update` referenced in templates but route does not exist; `app_animal_edit` handles both GET and POST. |
| 0.75 | NEW | Missing CSRF token in `PlanteController::new` POST route (form validation present but no explicit token check). |
| 0.7 | NEW | Possible Doctrine mapping naming conflict: `Ferme::$id` mapped to column `id_ferme` (property/column name mismatch may cause ORM hydration issues). |
| 0.7 | NEW | Duplicate route name `index` used in multiple Admin controllers (`Admin/UserController`, `Admin/StatisticsController`, `Admin/KanbanController`) – may cause URL generation ambiguity. |
| 0.7 | NEW | Duplicate route name `index` used in public controllers (`AnimalController`, `PlanteController`, `FermeController`) – potential naming collisions in URL generation. |
| 0.65 | NEW | Cross‑file call duplication: `UserService` injected in `Admin/DashboardController` and `Admin/UserController` with similar user‑statistics logic. |
| 0.65 | NEW | Missing `UserRepository` use statement in `FermeController` (type‑hinted in method but not imported at top). |
| 0.6 | NEW | Undefined variable risk: `$fermeRepository` in `FermeController::index` not type‑hinted in method signature (relies on autowiring). |
| 0.6 | NEW | Duplicate form handling pattern: `new` and `edit` methods in `AnimalController`, `PlanteController`, `FermeController` share identical logic. |
| 0.55 | NEW | Missing repository method `search` referenced in `AnalyseController::index` (line 27). |
| 0.5 | NEW | Potential N+1 query in `animal/index.html.twig` and `plante/index.html.twig` (looping over entities with property access). |
| 0.5 | NEW | Hardcoded database credentials in `.env` (root with no password) – security risk. |
| 0.4 | NEW | Missing `edit.html.twig` for `Conseil` and `Analyse` entities (only `new.html.twig` and `show.html.twig` exist). |
