# 🚀 FarmAI — Real Execution Guide

This guide ensures that your application is always ready for a live demo, including real data in the database, even after a computer restart.

## 🏁 The One-Click Start
I have created a script called **`RUN_APP.bat`** in your project root. 
Whenever you want to start your app, just double-click it (or run it in terminal).

**What it does automatically:**
1.  **Clears Symfony Cache** to avoid route/template errors.
2.  **Seeds the Database**: It runs `setup_demo_data.php` which:
    - Creates an Admin user: `admin@farmai.com` / `admin123`.
    - Creates **Analyse ID #1** (This is crucial! Your demo URLs depend on this ID).
    - Injects real symptoms and councils for the AI demo.
3.  **Launches the Server**: Starts the `symfony serve` command.

---

## 🔑 Login Credentials
Use these to log in during the demo:
- **URL**: `http://localhost:8000/login`
- **Email**: `admin@farmai.com`
- **Password**: `admin123`

---

## 🛠️ Manual Commands
If you want to run things manually, use these shortcuts:

- **Run any Symfony command**: Use `sf`
  - *Example:* `./sf debug:router`
- **Reset Demo Data only**: 
  - `& "C:\xampp\New folder\php82\php.exe" setup_demo_data.php`

---

## 📁 Shared Java/Web Database
The app is configured to use the `farmai` database on `127.0.0.1`.
- If your Java application modifies the database, the Web app will see those changes live.
- The `RUN_APP.bat` script includes a schema check to warn you if any tables are missing.

---

## 🖥️ Demo Center
Once the app is running, visit:
**[http://localhost:8000/admin/demo/quick-ref](http://localhost:8000/admin/demo/quick-ref)**
This is your command center for the entire presentation.
