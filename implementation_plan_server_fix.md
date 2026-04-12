# Implementation Plan - Server Configuration Fix

[Overview]
Fix the PHP built-in server configuration to properly route requests through Symfony's kernel, ensuring Twig templates render as HTML instead of displaying raw code.

The issue occurs because the PHP development server at `localhost:8000` is not using the `public/router.php` file. When accessing `/plante/`, the server serves the raw Twig template directly instead of routing the request through Symfony's front controller. The `public/router.php` file contains the logic to bootstrap the Symfony kernel and handle all dynamic requests properly.

[Types]
No type system changes required.

[Files]
No file modifications required - only server process management.

Files involved:
- `public/router.php` [CONFIRMED] - Existing Symfony router for PHP built-in server
- `public/index.php` [CONFIRMED] - Symfony front controller

[Functions]
No function modifications required.

[Classes]
No class modifications required.

[Dependencies]
No dependency changes required.

[Testing]
Verify fix by accessing `http://localhost:8000/plante/` and confirming:
1. Page renders as HTML (not raw Twig code)
2. All template variables are properly evaluated
3. CSS and assets load correctly

[Implementation Order]
Single step: Stop the current PHP server and restart with the correct router configuration.

Steps:
1. Identify and terminate any running PHP server processes on port 8000
2. Start new PHP server with router: `php -S localhost:8000 -t public public/router.php`
3. Verify `http://localhost:8000/plante/` renders HTML correctly
4. Confirm no raw Twig code is displayed
