#!/bin/bash

# Step 3: Deploy on server
echo "Step 3: Extracting and configuring on server..."

# Server details
HOST="195.35.57.85"
PORT="65002"
USER="u221943340"
PASS="Mirxa420$"

# Connect to server and deploy
ssh -p $PORT $USER@$HOST << 'EOF'
echo "Connected to server successfully"
cd /home/u221943340/domains/go.habibistay.com/public_html

echo "Backing up current installation..."
if [ -d "backup" ]; then rm -rf backup; fi
mkdir backup
cp -r . backup/ 2>/dev/null || true

echo "Extracting new files..."
tar -xzf /home/u221943340/habibistay-deploy.tar.gz

echo "Setting up environment..."
cp .env.production .env

echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running database migrations..."
php artisan migrate --force

echo "✅ Server deployment completed!"
EOF

echo "🎉 Full deployment completed!"
echo "Visit: https://go.habibistay.com"
