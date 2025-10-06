#!/bin/bash

set -e

php bin/console doctrine:migrations:current 2>/dev/null --quiet
MIGRATION_TABLE_EXISTS=$?

if [ "$MIGRATION_TABLE_EXISTS" -ne 0 ]; then
    echo "First run"
    echo "Installing Composer dependencies..."
    composer install --prefer-dist --no-interaction

    echo "Initializing database..."
    php bin/console doctrine:database:drop --force --if-exists
    php bin/console doctrine:database:create

    echo "Applying migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction

    echo "Running synchronization..."
    php bin/console app:sync-movies
fi
