#!/bin/bash

# --- LARAVEL UPDATE SCRIPT ---
# Use this after running 'git pull' to apply changes safely.

set -e

# Ensure script is run as root
if [ "$EUID" -ne 0 ]; then
  echo "Please run as root (use sudo)"
  exit 1
fi

PROJECT_PATH=$(pwd)

echo "--- 1. Pulling latest changes ---"
# Note: If you have local changes on the VM, this might fail. 
# Best practice is to keep the VM clean.
sudo -u www-data git pull

echo "--- 2. Updating Dependencies ---"
sudo -u www-data composer install --no-dev --optimize-autoloader
npm install
npm run build

echo "--- 3. Running Migrations ---"
sudo -u www-data php artisan migrate --force

echo "--- 4. Re-Optimizing Application ---"
# Clear old caches first
sudo -u www-data php artisan optimize:clear

# Re-cache everything for production speed
sudo -u www-data php artisan optimize
sudo -u www-data php artisan event:cache

echo "--- 5. Restarting Workers ---"
# This restarts the queue workers so they use the new code
sudo supervisorctl restart all

echo "--- 6. Fixing Permissions ---"
chmod -R 775 $PROJECT_PATH/storage $PROJECT_PATH/bootstrap/cache
chown -R www-data:www-data $PROJECT_PATH

echo "--- UPDATE COMPLETE ---"
echo "Your application has been updated and re-optimized."
