#!/bin/bash

# GeoCasa Bohol - Demo Setup Script
# Run this script to prepare your application for the capstone defense demo

echo "🚀 Setting up GeoCasa Bohol for Demo..."

# 1. Clear and optimize cache
echo "📦 Optimizing application cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 2. Clear existing demo data
echo "🗑️ Clearing existing data..."
php artisan migrate:fresh --force

# 3. Seed demo data
echo "🌱 Seeding demo data..."
php artisan db:seed --class=DemoDataSeeder

# 4. Create storage symlink
echo "🔗 Creating storage symlink..."
php artisan storage:link

# 5. Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 755 public/storage

# 6. Clear and rebuild frontend assets
echo "🎨 Building frontend assets..."
npm run build

# 7. Start development server (optional)
echo "🌐 Starting development server..."
echo "Demo credentials:"
echo "Admin: admin@geocasabohol.com / password"
echo "Broker: maria@geocasabohol.com / password"
echo "Broker: juan@geocasabohol.com / password"
echo "Broker: pedro@geocasabohol.com / password"
echo ""
echo "Demo is ready! Visit: http://localhost:8000"
echo "Press Ctrl+C to stop the server"

# Uncomment the next line to automatically start the server
# php artisan serve
