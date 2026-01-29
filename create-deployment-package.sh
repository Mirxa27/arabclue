#!/bin/bash

# HabibiStay Deployment Package Creator
# Creates a production-ready zip file for deployment

echo "🏠 Creating HabibiStay Deployment Package..."
echo "==============================================="

# Set variables
PACKAGE_NAME="habibistay-v1.0.0-$(date +%Y%m%d)"
TEMP_DIR="/tmp/$PACKAGE_NAME"
CURRENT_DIR=$(pwd)

# Create temporary directory
echo "📁 Creating temporary package directory..."
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR"

# Copy application files
echo "📋 Copying application files..."
cp -r app "$TEMP_DIR/"
cp -r bootstrap "$TEMP_DIR/"
cp -r config "$TEMP_DIR/"
cp -r database "$TEMP_DIR/"
cp -r install "$TEMP_DIR/"
cp -r public "$TEMP_DIR/"
cp -r resources "$TEMP_DIR/"
cp -r routes "$TEMP_DIR/"
cp -r storage "$TEMP_DIR/"
cp -r tests "$TEMP_DIR/"

# Copy important files
echo "📄 Copying configuration files..."
cp artisan "$TEMP_DIR/"
cp composer.json "$TEMP_DIR/"
cp composer.lock "$TEMP_DIR/"
cp .env.example "$TEMP_DIR/"
cp .gitignore "$TEMP_DIR/"
cp phpunit.xml "$TEMP_DIR/"

# Create deployment README
echo "📖 Creating deployment documentation..."
cat > "$TEMP_DIR/DEPLOYMENT_README.md" << 'EOF'
# HabibiStay Deployment Guide

## 🚀 Quick Installation

1. **Upload Files**: Extract this package to your web server
2. **Set Permissions**: Ensure storage/ and bootstrap/cache/ are writable
3. **Install Dependencies**: Run `composer install --no-dev --optimize-autoloader`
4. **Run Installer**: Navigate to `/install` in your browser
5. **Follow Wizard**: Complete the step-by-step installation

## 📋 Server Requirements

- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- OpenSSL Extension
- PDO Extension
- Mbstring Extension
- Tokenizer Extension
- XML Extension
- Ctype Extension
- JSON Extension
- BCMath Extension

## 🔧 Installation Steps

### 1. Web Server Setup
Point your domain to the `public/` directory:
```
DocumentRoot /path/to/habibistay/public
```

### 2. File Permissions
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### 3. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### 4. Run Installation Wizard
Navigate to: `https://yourdomain.com/install`

The wizard will guide you through:
- Requirements check
- File permissions verification
- Database configuration
- Admin account creation
- Service configuration

### 5. Security (Important!)
After installation:
- Remove the `/install` directory
- Set up SSL/HTTPS
- Configure firewall rules
- Set up regular backups

## 🌟 Features Included

- **Property Management**: Complete rental property system
- **Booking Engine**: Advanced booking and payment processing
- **Sara AI Chatbot**: Intelligent customer support
- **Multi-language Support**: Arabic and English
- **Payment Gateways**: PayPal and MyFatoorah integration
- **Admin Dashboard**: Comprehensive management interface
- **Host Portal**: Property owner management tools
- **Mobile Ready**: Responsive design with PWA support

## 🔐 Default Admin Credentials
Will be created during installation wizard.

## 📞 Support
For technical support: support@habibistay.com

## 📄 License
HabibiStay Enterprise License v1.0
EOF

# Create production .env template
echo "🔧 Creating production environment template..."
cat > "$TEMP_DIR/.env.production" << 'EOF'
APP_NAME="HabibiStay"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=habibistay
DB_USERNAME=habibistay_user
DB_PASSWORD=secure_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Configure during installation
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# AI Configuration (Optional)
OPENAI_API_KEY=
SARA_ENABLED=true

# Payment Gateways (Configure as needed)
PAYPAL_MODE=live
PAYPAL_CLIENT_ID=
PAYPAL_CLIENT_SECRET=

MYFATOORAH_API_KEY=
MYFATOORAH_MODE=live
MYFATOORAH_COUNTRY=SA

# File Storage (Optional - AWS S3)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

# Support Configuration
HABIBISTAY_SUPPORT_EMAIL=support@yourdomain.com
HABIBISTAY_SUPPORT_PHONE="+966550800669"
HABIBISTAY_NOREPLY_EMAIL=noreply@yourdomain.com
EOF

# Clean up development files
echo "🧹 Cleaning up development files..."
find "$TEMP_DIR" -name ".DS_Store" -delete
find "$TEMP_DIR" -name "*.log" -delete
rm -rf "$TEMP_DIR/storage/logs/*"
rm -rf "$TEMP_DIR/storage/framework/cache/data/*"
rm -rf "$TEMP_DIR/storage/framework/sessions/*"
rm -rf "$TEMP_DIR/storage/framework/views/*"

# Create necessary directories
echo "📁 Creating required directories..."
mkdir -p "$TEMP_DIR/storage/framework/cache/data"
mkdir -p "$TEMP_DIR/storage/framework/sessions"
mkdir -p "$TEMP_DIR/storage/framework/views"
mkdir -p "$TEMP_DIR/storage/logs"
mkdir -p "$TEMP_DIR/bootstrap/cache"

# Create .gitkeep files for empty directories
touch "$TEMP_DIR/storage/framework/cache/data/.gitkeep"
touch "$TEMP_DIR/storage/framework/sessions/.gitkeep"
touch "$TEMP_DIR/storage/framework/views/.gitkeep"
touch "$TEMP_DIR/storage/logs/.gitkeep"

# Create deployment script
echo "🚀 Creating deployment helper script..."
cat > "$TEMP_DIR/deploy.sh" << 'EOF'
#!/bin/bash

echo "🏠 HabibiStay Deployment Script"
echo "==============================="

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer not found. Please install Composer first."
    exit 1
fi

# Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Set permissions
echo "🔐 Setting file permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Create symbolic link for storage
if [ ! -L "public/storage" ]; then
    echo "🔗 Creating storage symlink..."
    php artisan storage:link
fi

echo "✅ Deployment preparation complete!"
echo "🌐 Navigate to /install to complete setup"
EOF

chmod +x "$TEMP_DIR/deploy.sh"

# Create installation completion script
echo "🔧 Creating post-installation script..."
cat > "$TEMP_DIR/post-install.sh" << 'EOF'
#!/bin/bash

echo "🏠 HabibiStay Post-Installation Setup"
echo "====================================="

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set final permissions
echo "🔐 Setting production permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod 644 .env

echo "✅ Post-installation optimization complete!"
echo "🔒 Remember to remove the /install directory for security"
EOF

chmod +x "$TEMP_DIR/post-install.sh"

# Create version info
echo "📊 Creating version information..."
cat > "$TEMP_DIR/VERSION" << EOF
HabibiStay Enterprise v1.0.0
Build Date: $(date)
Package: $PACKAGE_NAME
Platform: Property Rental Management System
License: HabibiStay Enterprise License
EOF

# Create package info
echo "📋 Creating package manifest..."
cat > "$TEMP_DIR/PACKAGE_INFO.json" << EOF
{
    "name": "HabibiStay",
    "version": "1.0.0",
    "type": "property-rental-platform",
    "build_date": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
    "package_name": "$PACKAGE_NAME",
    "description": "Complete property rental management platform with AI assistance",
    "features": [
        "Property Management",
        "Booking System",
        "Payment Processing",
        "Sara AI Chatbot",
        "Admin Dashboard",
        "Host Portal",
        "Mobile Support",
        "Multi-language"
    ],
    "requirements": {
        "php": ">=8.2",
        "mysql": ">=5.7",
        "composer": ">=2.0"
    },
    "license": "HabibiStay Enterprise License v1.0"
}
EOF

# Create the zip package
echo "📦 Creating deployment package..."
cd "$(dirname "$TEMP_DIR")"
zip -r "$CURRENT_DIR/$PACKAGE_NAME.zip" "$(basename "$TEMP_DIR")" -q

# Clean up
echo "🧹 Cleaning up temporary files..."
rm -rf "$TEMP_DIR"

# Final output
echo ""
echo "✅ Deployment package created successfully!"
echo "📦 Package: $PACKAGE_NAME.zip"
echo "📏 Size: $(du -h "$CURRENT_DIR/$PACKAGE_NAME.zip" | cut -f1)"
echo ""
echo "🚀 Ready for deployment!"
echo "📖 See DEPLOYMENT_README.md in the package for installation instructions"
echo ""
echo "🔑 Installation URL: https://yourdomain.com/install"
echo ""