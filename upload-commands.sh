#!/bin/bash

# Quick upload commands for HabibiStay deployment
echo "🚀 HabibiStay Upload Commands"
echo "============================="

echo "📦 Package: habibistay-20250609_064220.tar.gz"
echo "📊 Server: 195.35.57.85:65002"
echo "👤 User: u221943340"
echo "📁 Path: /home/u221943340/domains/go.habibistay.com/public_html"
echo ""

echo "🔑 SSH Connection:"
echo "ssh -p 65002 u221943340@195.35.57.85"
echo "Password: Mirxa420$"
echo ""

echo "📤 Upload Package:"
echo "scp -P 65002 ../habibistay-20250609_064220.tar.gz u221943340@195.35.57.85:/home/u221943340/"
echo ""

echo "📂 Extract on Server:"
echo "cd /home/u221943340/"
echo "tar -xzf habibistay-20250609_064220.tar.gz"
echo "mkdir -p domains/go.habibistay.com/public_html"
echo "cp -r * domains/go.habibistay.com/public_html/"
echo ""

echo "⚙️ Setup Commands:"
echo "cd domains/go.habibistay.com/public_html"
echo "cp .env.production .env"
echo "chmod -R 755 storage bootstrap/cache public"
echo "chmod 644 .env"
echo "php artisan migrate --force"
echo "php artisan key:generate --force"
echo "php artisan storage:link"
echo "php artisan config:cache"
echo ""

echo "🧪 Test URL: https://go.habibistay.com"