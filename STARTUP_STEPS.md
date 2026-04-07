# 🌿 FarmAI — IRL Startup After Restart
# Alaeddin | PIDEV 3A | Expert Module

═══════════════════════════════════════
EVERY TIME YOU START THE COMPUTER
DO THESE STEPS IN ORDER
═══════════════════════════════════════

## ① XAMPP First (2 minutes)

1. Find XAMPP on desktop or Start menu
2. Open XAMPP Control Panel
3. Click GREEN START button next to APACHE
   → Wait for green light ✅
4. Click GREEN START button next to MYSQL
   → Wait for green light ✅
5. Both must show green before continuing

   ⚠️ IF PORT ERROR on Apache:
   → Apache → Config → httpd.conf
   → Find "Listen 80" change to "Listen 8080"
   → Stop/Start Apache again

   ⚠️ IF PORT ERROR on MySQL:
   → Another MySQL is running
   → Open Task Manager → End "mysqld.exe"
   → Start MySQL again in XAMPP

═══════════════════════════════════════

## ② Open PowerShell (30 seconds)

1. Press Windows key
2. Type: PowerShell
3. Click: Windows PowerShell
4. You see a blue window with PS prompt

═══════════════════════════════════════

## ③ Navigate to Project (10 seconds)

Type EXACTLY this and press Enter:
   cd "C:\Users\sliti\Documents\web\pre-release\teammate-farmai"

You should see:
   PS C:\Users\sliti\Documents\web\pre-release\teammate-farmai>

═══════════════════════════════════════

## ④ Clear Cache (20 seconds)

Type EXACTLY this and press Enter:
   & "C:\xampp\New folder\php82\php.exe" bin/console cache:clear

Wait for:
   [OK] Cache for the "dev" environment was successfully cleared.

═══════════════════════════════════════

## ⑤ Start Server (10 seconds)

Type EXACTLY this and press Enter:
   symfony server:start --no-tls

Wait for:
   [OK] Web server listening
   The Web server is using PHP

═══════════════════════════════════════

## ⑥ Open Browser

1. Open Chrome or Firefox
2. Go to: http://localhost:8000
3. You see FarmAI page ✅

═══════════════════════════════════════

## ⑦ Login

1. Go to: http://localhost:8000/login
2. Email:    expert@farmai.tn
3. Password: (written below)
4. Click Login
5. You are redirected ✅

PASSWORD: ___________________________
(fill this in before demo day!)

═══════════════════════════════════════

## ⑧ Open Demo Tabs

Open these URLs in separate tabs:

TAB 1: http://localhost:8000/admin/analyse
TAB 2: http://localhost:8000/admin/analyse/1
TAB 3: http://localhost:8000/admin/analyse/1/ai-diagnostic
TAB 4: http://localhost:8000/admin/report/analyse/1/pdf
TAB 5: http://localhost:8000/admin/report/analyse/1/weather
TAB 6: http://localhost:8000/admin/conseil
TAB 7: http://localhost:8000/analyse
TAB 8: http://localhost:8000/expert/dashboard
TAB 9: https://github.com/dinalad042-bot/esprit-PIDEV-3A16-2526-FARMIA/tree/Alaeddin-expertise-branch

═══════════════════════════════════════

## ✅ YOU ARE READY FOR DEMO!

═══════════════════════════════════════

## 🚨 EMERGENCY FIXES

### Problem: "symfony not recognized"
Fix — type this instead:
   & "C:\xampp\New folder\php82\php.exe" -S localhost:8000 -t public

### Problem: "Port 8000 already in use"
Fix:
   symfony server:stop
   symfony server:start --no-tls
OR use different port:
   symfony server:start --no-tls --port=8001
Then use http://localhost:8001

### Problem: 500 Error on page
Fix:
   & "C:\xampp\New folder\php82\php.exe" bin/console cache:clear
   Refresh page

### Problem: Database empty / no data
Fix — open phpMyAdmin:
   http://localhost/phpmyadmin
   Select database: farmai
   Check table: analyse has rows
   If empty: import farmai_backup.sql

### Problem: AI Diagnostic timeout
Fix:
   Check internet connection
   Groq key is in .env.local file
   Try again (rate limit resets in 1 min)

### Problem: PDF shows white page
Fix:
   & "C:\xampp\New folder\php82\php.exe" "C:\Users\sliti\Documents\web\pre-release\composer.phar" dump-autoload
   Refresh PDF url

### Problem: Login fails
Fix:
   Check password with teammate
   Or use: admin@farmai.tn
   Check MySQL is running in XAMPP

### Problem: Dropdowns empty in form
Fix:
   MySQL must be running in XAMPP
   Check http://localhost/phpmyadmin loads
   Check farmai database exists

═══════════════════════════════════════

## 📂 IMPORTANT PATHS

Project folder:
C:\Users\sliti\Documents\web\pre-release\teammate-farmai

PHP executable:
C:\xampp\New folder\php82\php.exe

Composer:
C:\Users\sliti\Documents\web\pre-release\composer.phar

API Keys (never share!):
C:\Users\sliti\Documents\web\pre-release\teammate-farmai\.env.local

GitHub branch:
https://github.com/dinalad042-bot/esprit-PIDEV-3A16-2526-FARMIA/tree/Alaeddin-expertise-branch

═══════════════════════════════════════

## 💬 WHAT TO SAY IF ASKED

"My module is Expert — Gestion des Analyses
et Conseils Agricoles avec Intelligence Artificielle.

I built full CRUD for Analyse and Conseil entities
with a OneToMany relationship, integrated the Groq AI
API for plant disease diagnosis using both text and
vision modes, added OpenWeatherMap for agricultural
weather advice, and PDF export with DomPDF.

I performed 21 End-to-End tests — all passed.
The module shares the MySQL database with the
Java desktop application."

═══════════════════════════════════════
