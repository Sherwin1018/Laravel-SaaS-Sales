# Setup (clone / new machine)

After cloning the repo, run these steps so the app runs and the page loads.

## 1. Enable PHP extensions (fixes "Page isn't working")

The app needs **two PHP extensions** enabled. If the browser shows "Page isn't working" or database errors, enable them:

### Enable mbstring extension

- **Windows (XAMPP / PHP):** Edit `php.ini` (usually `C:\xampp\php\php.ini`), find:
  ```ini
  ;extension=mbstring
  ```
  Remove the semicolon:
  ```ini
  extension=mbstring
  ```

### Enable MySQL driver (pdo_mysql)

- **Windows (XAMPP / PHP):** In the same `php.ini`, find:
  ```ini
  ;extension=pdo_mysql
  ```
  Remove the semicolon:
  ```ini
  extension=pdo_mysql
  ```

**After editing:** Save `php.ini` and **restart your terminal** (close and reopen where you run `php artisan serve`).

**Verify both are enabled:**
```bash
php -m | findstr "mbstring pdo_mysql"
```
You should see both `mbstring` and `pdo_mysql` in the output.

**Note:** If you're using MySQL (not SQLite), you also need MySQL running in XAMPP Control Panel.

## 2. Install dependencies

```bash
composer install
```

## 3. Environment and key

```bash
copy .env.example .env
php artisan key:generate
```

(On macOS/Linux: `cp .env.example .env`)

## 4. Database (SQLite)

Create the SQLite file and run migrations:

```bash
# Windows PowerShell
New-Item -ItemType File -Path database\database.sqlite -Force

# Or create an empty file manually: database/database.sqlite
```

Then:

```bash
php artisan migrate
```

## 5. Run the app

```bash
php artisan serve
```

Open **http://localhost:8000** (with the port). The root URL redirects to the login page.

---

**Summary:** If the server starts but the page says "Page isn't working", enable **mbstring** in `php.ini` first, then ensure `.env` exists, `APP_KEY` is set, and the SQLite database file exists and migrations have been run.
