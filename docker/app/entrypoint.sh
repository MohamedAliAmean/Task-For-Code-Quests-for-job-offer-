#!/usr/bin/env bash
set -euo pipefail

if [[ -z "${APP_KEY:-}" ]]; then
  echo "APP_KEY is required." >&2
  exit 1
fi

if [[ "${DB_CONNECTION:-}" == "mysql" ]]; then
  echo "Waiting for MySQL (${DB_HOST:-db}:${DB_PORT:-3306})..."

  attempts=0
  until php -r '
    $host = getenv("DB_HOST") ?: "db";
    $port = getenv("DB_PORT") ?: "3306";
    $db = getenv("DB_DATABASE") ?: "";
    $user = getenv("DB_USERNAME") ?: "";
    $pass = getenv("DB_PASSWORD") ?: "";
    try {
      new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, [PDO::ATTR_TIMEOUT => 2]);
    } catch (Throwable) {
      exit(1);
    }
  '; do
    attempts=$((attempts+1))
    if [[ $attempts -ge 30 ]]; then
      echo "MySQL not ready after ${attempts} attempts." >&2
      exit 1
    fi
    sleep 2
  done
fi

if [[ "${RUN_MIGRATIONS:-false}" == "true" ]]; then
  php artisan migrate --force --no-interaction
fi

exec "$@"
