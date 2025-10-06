#!/bin/bash
set -e

echo "Starting setup..."
echo "======================================="

# --- Database ---
echo "1. Dropping and recreating the database..."
php bin/console doctrine:database:drop --force --if-exists
php bin/console doctrine:database:create

# --- Migrations ---
echo "2. Applying migrations (Schema update)..."
php bin/console doctrine:migrations:migrate --no-interaction

# --- Data Synchronization ---
echo "3. Running the initial movie synchronization..."
php bin/console app:sync-movies

echo "======================================="
echo "Setup complete!"
