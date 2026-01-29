#!/bin/bash

# HabibiStay Clean Deployment Script
# This script will clean the target directory and deploy fresh

echo "🚀 HabibiStay - Clean Deployment to Hostinger"
echo "=============================================="

# Server configuration
HOST="195.35.57.85"
PORT="65002"
USER="u221943340"
REMOTE_PATH="/home/u221943340/domains/go.habibistay.com/public_html"
PACKAGE_NAME="habibistay-clean-deploy-$(date +%Y%m%d_%H%M%S).tar.gz"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

echo_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

echo_error() {
    echo -e "${RED}❌ $1${NC}"
}

echo_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Step 1: Create deployment package
echo_info "Step 1: Creating clean deployment package..."

# Create temporary directory for packaging
TEMP_DIR=$(mktemp -d)
echo_info "Using temporary directory: $TEMP_DIR"

# Copy essential application files
echo_info "Copying application files..."
cp -r app "$TEMP_DIR/"
cp -r bootstrap "$TEMP_DIR/"
cp -r config "$TEMP_DIR/"
cp -r database "$TEMP_DIR/"
cp -r public "$TEMP_DIR/"
cp -r resources "$TEMP_DIR/"
cp -r routes "$TEMP_DIR/"
cp -r storage "$TEMP_DIR/"

# Copy configuration files
echo_info "Copying configuration files..."
cp artisan "$TEMP_DIR/"
cp composer.json "$TEMP_DIR/"
cp composer.lock "$TEMP_DIR/"
cp .env.production "$TEMP_DIR/"
cp phpunit.xml "$TEMP_DIR/"

# Clean storage directories
echo_info "Cleaning storage directories..."
rm -rf "$TEMP_DIR/storage/logs/"*
rm -rf "$TEMP_DIR/storage/framework/cache/"*
rm -rf "$TEMP_DIR/storage/framework/sessions/"*
rm -rf "$TEMP_DIR/storage/framework/views/"*

# Create necessary directories
mkdir -p "$TEMP_DIR/storage/logs"
mkdir -p "$TEMP_DIR/storage/framework/cache"
mkdir -p "$TEMP_DIR/storage/framework/sessions"
mkdir -p "$TEMP_DIR/storage/framework/views"

# Create deployment package
echo_info "Creating deployment archive..."
cd "$TEMP_DIR"
tar -czf "../$PACKAGE_NAME" .
cd - > /dev/null

# Move package to current directory
mv "$TEMP_DIR/../$PACKAGE_NAME" "./$PACKAGE_NAME"

# Clean up temp directory
rm -rf "$TEMP_DIR"

echo_success "Deployment package created: $PACKAGE_NAME"

# Step 2: Upload package to server
echo_info "Step 2: Uploading package to server..."

scp -P $PORT "./$PACKAGE_NAME" "$USER@$HOST:/home/$USER/"

if [ $? -eq 0 ]; then
    echo_success "Package uploaded successfully"
else
    echo_error "Failed to upload package"
    exit 1
fi

# Step 3: Connect to server and deploy
echo_info "Step 3: Connecting to server and deploying..."

ssh -p $PORT "$USER@$HOST" << 'EOF'
set -e

echo "🔗 Connected to server successfully"

# Navigate to home directory
cd /home/u221943340

echo "🧹 Step 1: Cleaning target directory..."
# Backup current installation if it exists
REMOTE_PATH="/home/u221943340/domains/go.habibistay.com/public_html"
if [ -d "$REMOTE_PATH" ] && [ "$(ls -A $REMOTE_PATH)" ]; then
    echo "📦 Creating backup of current installation..."
    BACKUP_DIR="/home/u221943340/backup-$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$BACKUP_DIR"
    cp -r "$REMOTE_PATH"/* "$BACKUP_DIR/" 2>/dev/null || true
    echo "✅ Backup created at: $BACKUP_DIR"
fi

# Clean the target directory completely
echo "🗑️  Cleaning target directory..."
rm -rf "$REMOTE_PATH"/*
rm -rf "$REMOTE_PATH"/.*  2>/dev/null || true

echo "📁 Step 2: Extracting new application..."
# Find the package file
PACKAGE_FILE=$(ls /home/u221943340/habibistay-clean-deploy-*.tar.gz | head -1)
if [ -z "$PACKAGE_FILE" ]; then
    echo "❌ Package file not found"
    exit 1
fi

# Extract the package
tar -xzf "$PACKAGE_FILE" -C "$REMOTE_PATH/"

# Navigate to application directory
cd "$REMOTE_PATH"

echo "⚙️  Step 3: Setting up environment..."
# Set up environment file
if [ -f ".env.production" ]; then
    cp .env.production .env
    echo "✅ Environment file configured"
else
    echo "❌ .env.production not found"
    exit 1
fi

echo "🔑 Step 4: Generating application key..."
php artisan key:generate --force

echo "🗄️  Step 5: Setting up database..."
# Run database migrations
php artisan migrate --force

echo "🚀 Step 6: Optimizing for production..."
# Clear all caches first
php artisan cache:clear || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Cache configurations for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔗 Step 7: Creating storage link..."
php artisan storage:link

echo "🔐 Step 8: Setting proper permissions..."
# Set proper permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
chmod 644 .env

echo "🧹 Step 9: Cleaning up..."
# Remove the uploaded package
rm -f "$PACKAGE_FILE"

echo ""
echo "✅ Clean deployment completed successfully!"
echo "🌐 Your site should now be accessible at: https://go.habibistay.com"
echo ""
echo "📋 Quick verification steps:"
echo "• Site homepage: https://go.habibistay.com"
echo "• Admin login: https://go.habibistay.com/admin/login"
echo "• API status: https://go.habibistay.com/api/status"
echo ""
EOF

# Clean up local package
rm -f "./$PACKAGE_NAME"

echo ""
echo_success "🎉 Deployment completed successfully!"
echo_info "🌐 Your application is now live at: https://go.habibistay.com"
echo ""
echo_warning "📝 Post-deployment checklist:"
echo "1. Test the homepage and main functionality"
echo "2. Verify admin login works"
echo "3. Check that the database is properly connected"
echo "4. Test API endpoints"
echo "5. Configure any missing environment variables"
echo ""
echo_info "🔍 To check logs if needed:"
echo "ssh -p $PORT $USER@$HOST"
echo "tail -f $REMOTE_PATH/storage/logs/laravel.log"
