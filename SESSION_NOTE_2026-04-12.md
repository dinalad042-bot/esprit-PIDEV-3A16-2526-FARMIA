PROJECT: FarmIA (Symfony 6.4 Application) | DATE: 2026-04-12 | STATUS: Phase 0 - Layer 1 - Server Configuration Fixed
Last trusted state: PHP server running with router.php on port 8000. Above = untrusted.

BLUEPRINT: Agricultural management system (Plantes/Fermes/Analyses) | Auth:symfony_security Storage:MySQL Deploy:PHP_builtin UI tone:Agricultural green Hero:Dashboard KPIs

LAYERS: [x] L1-Data [x] L2-Logic [ ] L3-IO [ ] L4-UI

FILE MAP (CRITICAL+WARN only):

- [Router]: public/router.php ← PHP builtin server router, bootstraps Symfony kernel [CONFIRMED]
- [Controller]: src/Controller/PlanteController.php ← Plant CRUD operations [CONFIRMED]
- [Template]: templates/plante/index.html.twig ← Plant management UI [CONFIRMED]
- [Entity]: src/Entity/Plante.php ← Plant data model [CONFIRMED]

FRAGILE POINTS:

- [WATCH] Server startup - Must use router.php or templates display raw code
  Mitigation: Always start with `php -S localhost:8000 -t public public/router.php`

DRIFT LOG: Server Layer: Fixed router configuration
UNCERTAINTIES: None
OPEN: Database connection needs MySQL running in XAMPP

RESUME: Paste this note. Add "Resume from note. Read FILE MAP and FRAGILE POINTS first."
