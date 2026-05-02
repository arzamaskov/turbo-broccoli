# Runtracker

Symfony 7.4 LTS, PHP 8.3, nginx and SQLite Docker environment for a server-rendered running training planner and analytics app.

## Development

```bash
make up
```

Open `http://localhost:8080/health`.

Useful commands:

```bash
make logs
make sh
make console
make composer require vendor/package
make down
```

SQLite is stored in the `sqlite_data` Docker volume and mounted at `var/data/app.db`.

Xdebug is installed in the dev PHP image and is disabled by default:

```bash
XDEBUG_MODE=debug make up
```

## Test

```bash
make test
```

Tests run inside the development PHP container with `APP_ENV=test` and a separate SQLite database at `var/test/test.db`.

## Production

Create a production env file:

```bash
cp .env.prod.example .env.prod
```

Set a strong `APP_SECRET`, then build and run:

```bash
make prod-build
make prod-up
```

The production compose file builds immutable PHP and nginx images, keeps SQLite/cache/logs in Docker volumes, disables debug mode, and uses PHP opcache settings suited for production.

## Notes

- The dev image bind-mounts the project and installs Composer dependencies on first start if `vendor/` is missing.
- The dev image includes Xdebug. Keep `XDEBUG_MODE=off` unless you are actively debugging.
- PHP images are based on Alpine to keep runtime size low for the small VPS target.
- The prod image installs only non-dev Composer dependencies during build.
- nginx only executes `public/index.php`; direct PHP file access is denied.
- For a real public deployment, put TLS and HTTP/2 at a reverse proxy/load balancer layer in front of this compose stack.
