#!/usr/bin/env sh
set -eu

run_as_app() {
  if [ "$(id -u)" = "0" ]; then
    su-exec app "$@"
  else
    "$@"
  fi
}

mkdir -p var/cache var/log var/data var/test

if [ "$(id -u)" = "0" ]; then
  chown -R app:app var/cache var/log var/data var/test /tmp/composer
fi

if [ "${APP_ENV:-dev}" != "prod" ] && [ -f composer.json ] && [ ! -f vendor/autoload.php ]; then
  run_as_app composer install --no-interaction --prefer-dist
fi

if [ -n "${DATABASE_URL:-}" ]; then
  case "$DATABASE_URL" in
    sqlite:///%kernel.project_dir%/*)
      db_path="${DATABASE_URL#sqlite:///%kernel.project_dir%/}"
      mkdir -p "$(dirname "$db_path")"
      if [ "$(id -u)" = "0" ]; then
        chown -R app:app "$(dirname "$db_path")"
      fi
      run_as_app touch "$db_path"
      ;;
    sqlite:////app/*)
      db_path="${DATABASE_URL#sqlite:////app/}"
      mkdir -p "$(dirname "$db_path")"
      if [ "$(id -u)" = "0" ]; then
        chown -R app:app "$(dirname "$db_path")"
      fi
      run_as_app touch "$db_path"
      ;;
  esac
fi

if [ "$(id -u)" = "0" ] && [ "${1:-}" != "php-fpm" ]; then
  exec su-exec app "$@"
fi

exec "$@"
