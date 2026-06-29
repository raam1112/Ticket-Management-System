#!/bin/bash

# Cache Laravel configurations for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations (creating tables in Supabase)
php artisan migrate --force

# Seed database with default data (idempotent, safe to run multiple times)
php artisan db:seed --force

# Start Laravel queue worker in the background (processes notifications/emails without blocking)
php artisan queue:work --tries=3 --timeout=90 &

# Start the Apache web server
apache2-foreground
