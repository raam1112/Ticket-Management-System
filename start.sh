#!/bin/bash

# Cache Laravel configurations for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations (creating tables in Supabase)
php artisan migrate --force

# Start the Apache web server
apache2-foreground
