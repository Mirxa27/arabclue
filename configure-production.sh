#!/bin/bash

# Post-Deployment Configuration Script for HabibiStay
# Run this after the initial deployment to configure API keys and settings

set -e

echo "🔧 HABIBI STAY - POST-DEPLOYMENT CONFIGURATION"
echo "=============================================="

# Server credentials
SERVER_HOST="195.35.57.85"
SERVER_PORT="65002"
SERVER_USER="u221943340"
SERVER_PASS="Mirxa420$"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[INPUT NEEDED]${NC} $1"
}

# Get API keys from user
echo ""
print_warning "Please provide the following API keys (press Enter to skip):"
echo ""

read -p "OpenAI API Key: " OPENAI_KEY
read -p "ElevenLabs API Key: " ELEVENLABS_KEY
read -p "FCM Server Key: " FCM_KEY

# Configure email settings
echo ""
print_warning "Email Configuration (press Enter for defaults):"
read -p "SMTP Host [smtp.gmail.com]: " SMTP_HOST
SMTP_HOST=${SMTP_HOST:-smtp.gmail.com}

read -p "SMTP Port [587]: " SMTP_PORT
SMTP_PORT=${SMTP_PORT:-587}

read -p "SMTP Username: " SMTP_USER
read -s -p "SMTP Password: " SMTP_PASS
echo ""

# Update environment file on server
print_status "Updating environment configuration on server..."

sshpass -p "$SERVER_PASS" ssh -p "$SERVER_PORT" -o StrictHostKeyChecking=no \
    "$SERVER_USER@$SERVER_HOST" << ENDSSH

cd /home/u221943340/domains/go.habibistay.com/public_html

# Backup current .env
cp .env .env.backup-\$(date +%Y%m%d-%H%M%S)

# Update API keys
if [ -n "$OPENAI_KEY" ]; then
    sed -i "s/OPENAI_API_KEY=.*/OPENAI_API_KEY=$OPENAI_KEY/" .env
    echo "✅ OpenAI API key updated"
fi

if [ -n "$ELEVENLABS_KEY" ]; then
    sed -i "s/ELEVENLABS_API_KEY=.*/ELEVENLABS_API_KEY=$ELEVENLABS_KEY/" .env
    echo "✅ ElevenLabs API key updated"
fi

if [ -n "$FCM_KEY" ]; then
    sed -i "s/FCM_SERVER_KEY=.*/FCM_SERVER_KEY=$FCM_KEY/" .env
    echo "✅ FCM Server key updated"
fi

# Update email settings
if [ -n "$SMTP_USER" ]; then
    sed -i "s/MAIL_HOST=.*/MAIL_HOST=$SMTP_HOST/" .env
    sed -i "s/MAIL_PORT=.*/MAIL_PORT=$SMTP_PORT/" .env
    sed -i "s/MAIL_USERNAME=.*/MAIL_USERNAME=$SMTP_USER/" .env
    sed -i "s/MAIL_PASSWORD=.*/MAIL_PASSWORD=$SMTP_PASS/" .env
    sed -i "s/MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=tls/" .env
    echo "✅ Email settings updated"
fi

# Clear and recache configuration
php artisan config:clear
php artisan config:cache
php artisan cache:clear

echo "✅ Configuration updated successfully!"

ENDSSH

print_success "Configuration completed!"

echo ""
echo "🔍 TESTING CHECKLIST:"
echo "1. Visit: https://go.habibistay.com"
echo "2. Test admin login: https://go.habibistay.com/admin"
echo "3. Test Sara AI chatbot functionality"
echo "4. Test property listings and search"
echo "5. Test user registration and login"
echo "6. Test booking process"
echo ""
echo "🎉 Your HabibiStay platform is fully configured!"
