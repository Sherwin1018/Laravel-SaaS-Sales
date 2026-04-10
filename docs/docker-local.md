# Docker Local Run

This branch includes a local Docker Compose setup that runs:

- Laravel app on `http://localhost:8080`
- MySQL 8.4 on `localhost:3306`

## Start

```powershell
docker compose up --build
```

## Stop

```powershell
docker compose down
```

To also remove the MySQL data volume:

```powershell
docker compose down -v
```

## Important Environment Notes

`compose.yaml` overrides these values at runtime:

- `APP_URL=http://localhost:8080`
- `DB_HOST=mysql`
- `DB_PORT=3306`
- `DB_CONNECTION=mysql`
- `GOOGLE_REDIRECT_URI=http://localhost:8080/auth/google/callback`

Your `.env` can keep local non-Docker values for normal host runs.

## Google Sign-In

If Google OAuth is enabled, the Google console callback URL must include:

```text
http://localhost:8080/auth/google/callback
```

## Notes

- The app container runs migrations and seeders on startup, based on the existing `Dockerfile`.
- The MySQL service uses an empty root password to match the current local `.env` defaults.
- If port `3306` is already used on your machine, change the left side of the MySQL port mapping in `compose.yaml`.
