#!/bin/bash
# WiseKart AWS Production Deployment Script
set -e

echo "🚀 Starting Deployment for WiseKart..."

# Put app into maintenance mode
php artisan down --refresh=15 --retry=60 || true

# Pull latest changes (if running in git deployment hook)
# git pull origin main

# Install composer production dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Clear old caches
echo "🧹 Clearing old caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Optimize Laravel for production
echo "⚡ Caching configurations & routes for maximum performance..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Create storage symlink if not existing
php artisan storage:link || true

# Bring app back online
php artisan up

echo "✅ WiseKart successfully deployed to AWS Production!"
