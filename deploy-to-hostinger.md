# HabibiStay Hostinger Deployment Guide

## 🚀 Quick Deployment Steps

### Server Details
- **Host**: 195.35.57.85:65002
- **User**: u221943340
- **Password**: Mirxa420$
- **Domain**: go.habibistay.com
- **Database**: u221943340_newa
- **DB Host**: srv1730.hstgr.io

### Step 1: Create Deployment Package
```bash
cd /Users/abdullahmirxa/Desktop
tar -czf habibistay-production.tar.gz \
    --exclude='habibi-lara/node_modules' \
    --exclude='habibi-lara/.git' \
    --exclude='habibi-lara/storage/logs/*.log' \
    --exclude='habibi-lara/storage/framework/cache/*' \
    --exclude='habibi-lara/storage/framework/sessions/*' \
    --exclude='habibi-lara/storage/framework/views/*' \
    --exclude='habibi-lara/tests' \
    --exclude='habibi-lara/.env' \
    --exclude='habibi-lara/*.md' \
    habibi-lara/
```

### Step 2: Upload to Server
```bash
scp -P 65002 habibistay-production.tar.gz u221943340@195.35.57.85:/home/u221943340/
```

### Step 3: SSH to Server and Setup
```bash
ssh -p 65002 u221943340@195.35.57.85
```

### Step 4: Extract and Configure on Server
```bash
cd /home/u221943340/
tar -xzf habibistay-production.tar.gz
rm -rf domains/go.habibistay.com/public_html/*
cp -r habibi-lara/* domains/go.habibistay.com/public_html/
cd domains/go.habibistay.com/public_html

# Setup environment
cp .env.production .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# Set permissions
chmod -R 755 .
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

### Step 5: Database Setup (if needed)
```sql
-- Connect to database and create tables if needed
-- The migrations should handle this automatically
```

### Step 6: Test the Deployment
Visit: https://go.habibistay.com

### Production Environment Variables (.env.production)
```
APP_NAME="HabibiStay"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://go.habibistay.com

DB_CONNECTION=mysql
DB_HOST=srv1730.hstgr.io
DB_PORT=3306
DB_DATABASE=u221943340_newa
DB_USERNAME=u221943340_newa
DB_PASSWORD=Mirxa420$

CACHE_DRIVER=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
LOG_LEVEL=error
```

## ✅ Deployment Checklist
- [ ] Package created and uploaded
- [ ] Files extracted to public_html
- [ ] Environment configured
- [ ] Database connected
- [ ] Migrations run
- [ ] Cache optimized
- [ ] Permissions set
- [ ] Site accessible

## 🔧 Troubleshooting
- If site shows 500 error, check storage permissions
- If database errors, verify connection settings
- If assets missing, run `php artisan storage:link`
- Check logs in `storage/logs/laravel.log`
