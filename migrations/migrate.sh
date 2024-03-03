#!/bin/sh
set -e

# Wait for DB to become available
echo "Waiting for Postgres at $DB_HOST..."
until PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" -c '\q' 2>/dev/null
do
  sleep 1
done

echo "DB is ready. Starting migrations..."

for file in /app/migrations/*.sql
do
  echo "Running migration: $file"
  PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" -f "$file"
done

echo "All migrations applied successfully!"

exec "$@"