@echo off
title FarmAI Startup — Alaeddin PIDEV
color 0A
cls

echo.
echo  ==========================================
echo   FARMAI STARTUP SEQUENCE
echo   Alaeddin - Expert Module - PIDEV 3A
echo  ==========================================
echo.
echo  STEP 1: Checking PHP...
"C:\xampp\New folder\php82\php.exe" --version
if %errorlevel% neq 0 (
    color 0C
    echo  [FAIL] PHP not found!
    echo  Fix: Check C:\xampp\New folder\php82\php.exe exists
    pause
    exit /b 1
)
echo  [OK] PHP found!
echo.

echo  STEP 2: Clearing cache...
cd /d "C:\Users\sliti\Documents\web\pre-release\teammate-farmai"
"C:\xampp\New folder\php82\php.exe" bin/console cache:clear --no-warmup 2>nul
echo  [OK] Cache cleared!
echo.

echo  STEP 3: Starting server...
echo.
echo  ==========================================
echo   APP URL: http://localhost:8000
echo.  
echo   LOGIN:   http://localhost:8000/login
echo   EMAIL:   expert@farmai.tn
echo.
echo   KEY DEMO URLS:
echo   /admin/analyse
echo   /admin/conseil  
echo   /expert/dashboard
echo   /admin/analyse/1/ai-diagnostic
echo   /admin/report/analyse/1/pdf
echo   /admin/report/analyse/1/weather
echo  ==========================================
echo.
echo  Press Ctrl+C to stop server when done.
echo.

symfony server:start --no-tls

pause
