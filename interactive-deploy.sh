#!/bin/bash

echo "🚀 HabibiStay Interactive Deployment"
echo "===================================="
echo ""
echo "This script will connect to your server and execute the deployment."
echo "You'll need to enter the password when prompted: Mirxa420$"
echo ""
echo "Press Enter to continue..."
read

echo "🔗 Connecting to server..."
echo "Password: Mirxa420$"
echo ""

ssh -p 65002 u221943340@195.35.57.85 << 'EOF'
set -e

echo ""
echo "🔗 ✅ Connected to server successfully!"
echo ""

# Navigate to home directory
cd /home/u221943340
echo "📂 Current directory: $(pwd)"

# Step 1: Create backup of current installation
echo ""
echo "📦 Step 1: Creating backup of current installation..."
BACKUP_DIR="/home/u221943340/backup-$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
cp -r /home/u221943340/domains/go.habibistay.com/public_html/* "$BACKUP_DIR/" 2>/dev/null || true
echo "✅ Backup created at: $BACKUP_DIR"

# Step 2: Clean target directory completely
echo ""
echo "🗑️  Step 2: Cleaning target directory completely..."
rm -rf /home/u221943340/domains/go.habibistay.com/public_html/*
rm -rf /home/u221943340/domains/go.habibistay.com/public_html/.* 2>/dev/null || true
echo "✅ Target directory cleaned"

# Step 3: Extract new application
echo ""
echo "📁 Step 3: Extracting new application..."
tar -xzf habibistay-clean-deploy-20250609_075746.tar.gz -C /home/u221943340/domains/go.habibistay.com/public_html/
echo "✅ Application extracted"

# Step 4: Navigate to application directory
cd /home/u221943340/domains/go.habibistay.com/public_html
echo "📂 Moved to application directory: $(pwd)"

# Step 5: Set up environment
echo ""
echo "⚙️  Step 5: Setting up environment..."
cp .env.production .env
echo "✅ Environment file configured"

# Step 6: Generate application key
echo ""
echo "🔑 Step 6: Generating application key..."
php artisan key:generate --force
echo "✅ Application key generated"

# Step 7: Set up database
echo ""
echo "🗄️  Step 7: Setting up database..."
php artisan migrate --force
echo "✅ Database migrations completed"

# Step 8: Clear caches
echo ""
echo "🧹 Step 8: Clearing caches..."
php artisan cache:clear || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
echo "✅ Caches cleared"

# Step 9: Optimize for production
echo ""
echo "🚀 Step 9: Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Production optimization completed"

# Step 10: Create storage link
echo ""
echo "🔗 Step 10: Creating storage link..."
php artisan storage:link
echo "✅ Storage link created"

# Step 11: Set proper permissions
echo ""
echo "🔐 Step 11: Setting proper permissions..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
chmod 644 .env
echo "✅ Permissions set"

# Step 12: Clean up
echo ""
echo "🧹 Step 12: Cleaning up..."
rm -f /home/u221943340/habibistay-clean-deploy-20250609_075746.tar.gz
echo "✅ Cleanup completed"

# Final verification
echo ""
echo "🔍 Final verification:"
echo "- Application directory: $(pwd)"
echo "- Index file exists: $(ls -la public/index.php 2>/dev/null && echo 'YES' || echo 'NO')"
echo "- Environment file: $(ls -la .env 2>/dev/null && echo 'YES' || echo 'NO')"
echo "- Storage permissions: $(ls -ld storage/ | cut -d' ' -f1)"

echo ""
echo "🎉 ==============================================="
echo "✅ DEPLOYMENT COMPLETED SUCCESSFULLY!"
echo "🌐 Your site is now live at: https://go.habibistay.com"
echo "🎉 ==============================================="
echo ""
echo "📋 Quick verification URLs:"
echo "• Homepage: https://go.habibistay.com"
echo "• Admin Login: https://go.habibistay.com/admin/login"
echo "• API Status: https://go.habibistay.com/api/status"
echo ""

EOF

echo ""
echo "🎉 Deployment script completed!"
echo "🌐 Check your site at: https://go.habibistay.com"
