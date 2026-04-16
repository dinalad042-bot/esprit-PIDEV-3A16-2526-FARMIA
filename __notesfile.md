# Project Findings (auto‑generated)

| Confidence | Status | Finding |
|------------|--------|---------|
| 0.7 | NEW | Duplicate method `index` found in multiple Controller classes (possible duplicate logic). |
| 0.7 | NEW | Potential undefined variable `$em` in `src/Controller/AnimalController.php`. |
| 0.7 | NEW | Broken include: `templates/partials/header.html.twig` referenced but file missing. |
| 0.6 | NEW | Twig variable `animal_edit` used in `templates/animal/index.html.twig` without a guaranteed controller value (may be undefined). |
| 0.6 | NEW | Duplicate query logic: `findBySearchAndSort` pattern appears in both `AnimalController` and `PlanteController`. |
| 0.6 | NEW | Route naming conflict: `app_animal_edit` and `app_animal_update` (both point to edit action) – could cause ambiguous URL generation. |
| 0.6 | NEW | Missing CSRF token check in `AnimalController::generatePdf` (no token verification for a GET that triggers state‑changing code). |
| 0.6 | NEW | In `ConseilController`, raw query builder uses field `prioriteRaw` – possible typo (field likely `priorite`). |
| 0.6 | NEW | Duplicate Twig block for “alertes santé” – same logic repeated in `animal/index.html.twig` and `plante/index.html.twig`. |
| 0.6 | NEW | Unused dependency `EntityManagerInterface $em` in `AnalyseController` (constructor injection not used). |
| 0.5 | NEW | Possible Doctrine mapping naming conflict: property `$id` in `Ferme` entity is mapped to column `id_ferme` (property/column name mismatch may cause ORM issues). |
