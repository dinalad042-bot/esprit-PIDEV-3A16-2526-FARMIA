Would an agent likely miss this without help? Yes. This is a Symfony 6.4 PHP app (PHP 8.1+). Work from repo root.
Would an agent likely miss this without help? Yes. Install deps with `composer install` before running any app commands.
Would an agent likely miss this without help? Yes. Run the app from project root: `php -S localhost:8000 -t public public/router.php`. Port 8000 must be free.
Would an agent likely miss this without help? Yes. Docker-compose defines Postgres, but `.env` uses MySQL/MariaDB. Verify and align DB config.
Would an agent likely miss this without help? Yes. Run tests with `php bin/phpunit` (or `php bin/phpunit tests/Staging/` for staging subset). Run a single test: `php bin/phpunit tests/Staging/ExpertAIConnectionTest.php`.
Would an agent likely miss this without help? Yes. Staging tests need Python API on port 5000. Start it with: `cd python_api && source venv/bin/activate && python app.py` (or use the venv there).
Would an agent likely miss this without help? Yes. Copy `.env.example` to `.env` and set credentials (GROQ_API_KEY, GROQ_MODEL, PYTHON_API_URL, etc.).
Would an agent likely miss this without help? Yes. Codebase structure: src/Controller, src/Entity, src/Service, templates/, config/, tests/, public/.
Would an agent likely miss this without help? Yes. Staging tests in tests/Staging/ (ExpertAIConnectionTest.php, ExpertModuleHandshakeTest.php).
Would an agent likely miss this without help? Yes. Demo data in `demo_data.sql` for database seeding.