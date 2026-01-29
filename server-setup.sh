#!/bin/bash

# HabibiStay Server Setup Script
# Run this script ON THE HOSTINGER SERVER after uploading files

echo "🚀 HabibiStay - Server Setup Script"
echo "==================================="
echo "Running on Hostinger server..."

# Check if we're in the right location
if [ ! -f "/home/u221943340/habibistay-production.tar.gz" ]; then
    echo "❌ Package not found. Please upload habibistay-production.tar.gz first"
    exit 1
fi

echo "📂 Step 1: Extracting files..."
cd /home/u221943340/
tar -xzf habibistay-production.tar.gz

echo "🗂️ Step 2: Moving files to web directory..."
rm -rf domains/go.habibistay.com/public_html/*
cp -r habibi-lara/* domains/go.habibistay.com/public_html/
cd domains/go.habibistay.com/public_html

echo "⚙️ Step 3: Setting up environment..."
if [ -f ".env.production" ]; then
    cp .env.production .env
    echo "✅ Environment file configured"
else
    echo "❌ .env.production not found"
    exit 1
fi

echo "🔑 Step 4: Generating application key..."
php artisan key:generate --force

echo "🗄️ Step 5: Running database migrations..."
php artisan migrate --force

echo "🚀 Step 6: Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔗 Step 7: Creating storage link..."
php artisan storage:link

echo "🔐 Step 8: Setting permissions..."
chmod -R 755 .
chmod -R 777 storage
chmod -R 777 bootstrap/cache

echo "🧹 Step 9: Cleaning up..."
cd /home/u221943340/
rm -rf habibi-lara/
rm habibistay-production.tar.gz

echo ""
echo "✅ Deployment completed successfully!"
echo "🌐 Your site should now be accessible at: https://go.habibistay.com"
echo ""
echo "📋 Quick checks:"
echo "• Check if site loads: https://go.habibistay.com"
echo "• Check admin login: https://go.habibistay.com/admin/login"
echo "• Check API status: https://go.habibistay.com/api/status"
echo ""
echo "🔧 If you encounter issues:"
echo "• Check storage/logs/laravel.log for errors"
echo "• Verify database connection"
echo "• Ensure permissions are correct"
