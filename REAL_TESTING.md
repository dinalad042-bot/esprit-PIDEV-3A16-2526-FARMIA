# 🧪 FarmAI — Real Testing Guide

To perform a "real test" for your PIDEV evaluation, use the specific PHP environment found on your system: `C:\xampp\New folder\php82\php.exe`.

## 1. Automated Testing (PHPUnit)
These tests verify that your code logic is correct.

### Run all tests:
```powershell
& "C:\xampp\New folder\php82\php.exe" bin/phpunit
```

### What I've added for you:
- `tests/Entity/AnalyseTest.php`: Verifies entity data logic.
- `tests/Controller/QuickRefControllerTest.php`: Verifies routes and security.

---

## 2. Manual "Real-World" Test (Demo Flow)
Follow this flow to ensure everything works exactly as the jury will see it:

1.  **Clear Cache**:
    ```powershell
    & "C:\xampp\New folder\php82\php.exe" bin/console cache:clear
    ```
2.  **Login as Admin**:
    - Go to `http://localhost:8000/login`
    - Verify your credentials.
3.  **Check the "Demo Center"**:
    - Go to `http://localhost:8000/admin/demo/quick-ref`
    - Click each button in sequence.
4.  **AI Diagnostic**:
    - Click the **AI DEMO** button.
    - Click "Copy" on the symptoms card.
    - Paste into the AI field and submit.
    - **Verify**: It should return a structured diagnostic (Séance 10 ⭐).
5.  **PDF/Weather**:
    - Ensure a PDF opens without errors.
    - Ensure weather data loads (requires internet for OpenWeather API).

---

## 3. Database Validation
Since you share the database with Java:
- Run `list_tables.php` or `describe_tables.php` (provided in root) to see if the schema matches your Java application.
- If you see migration errors, run:
  ```bash
  php bin/console doctrine:migrations:status
  ```

## 4. Environment Check
Ensure your `.env.local` contains the correct database URL:
```ini
DATABASE_URL="mysql://root:@127.0.0.1:3306/farmai_db?serverVersion=8.0.32&charset=utf8mb4"
```

---

> [!IMPORTANT]
> If `php` command is not recognized in your terminal, make sure you are in the environment where PHP is installed (e.g., inside XAMPP Shell, VS Code Terminal with PHP Path set, or your server environment).
