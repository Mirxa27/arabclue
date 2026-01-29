# 🚀 Execute Deployment - Copy & Paste Commands

## ✅ Package Upload Status: COMPLETED
The deployment package has been successfully uploaded to your server.

---

## 🔗 Step 1: Connect to Server

Run this command in your terminal:

```bash
ssh -p 65002 u221943340@195.35.57.85
```

**Password:** `Mirxa420$`

---

## 🚀 Step 2: Execute Deployment Commands

Once connected to the server, **copy and paste these commands one by one**:

### Navigate to home directory
```bash
cd /home/u221943340
```

### Create backup of current installation
```bash
echo "📦 Creating backup..."
BACKUP_DIR="/home/u221943340/backup-$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
cp -r /home/u221943340/domains/go.habibistay.com/public_html/* "$BACKUP_DIR/" 2>/dev/null || true
echo "✅ Backup created at: $BACKUP_DIR"
```

### Clean target directory completely
```bash
echo "🗑️  Cleaning target directory..."
rm -rf /home/u221943340/domains/go.habibistay.com/public_html/*
rm -rf /home/u221943340/domains/go.habibistay.com/public_html/.* 2>/dev/null || true
```

### Extract new application
```bash
echo "📁 Extracting new application..."
tar -xzf habibistay-clean-deploy-20250609_075746.tar.gz -C /home/u221943340/domains/go.habibistay.com/public_html/
```

### Navigate to application directory
```bash
cd /home/u221943340/domains/go.habibistay.com/public_html
```

### Set up environment
```bash
echo "⚙️  Setting up environment..."
cp .env.production .env
```

### Generate application key
```bash
echo "🔑 Generating application key..."
php artisan key:generate --force
```

### Set up database
```bash
echo "🗄️  Setting up database..."
php artisan migrate --force
```

### Optimize for production
```bash
echo "🚀 Optimizing for production..."
php artisan cache:clear || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
```

### Cache configurations
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Create storage link
```bash
echo "🔗 Creating storage link..."
php artisan storage:link
```

### Set proper permissions
```bash
echo "🔐 Setting proper permissions..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
chmod 644 .env
```

### Clean up
```bash
echo "🧹 Cleaning up..."
rm -f /home/u221943340/habibistay-clean-deploy-20250609_075746.tar.gz
```

### Final confirmation
```bash
echo ""
echo "✅ Clean deployment completed successfully!"
echo "🌐 Your site should now be accessible at: https://go.habibistay.com"
```

---

## 🔍 Step 3: Verify Deployment

After running all commands, test these URLs:

1. **Homepage**: https://go.habibistay.com
2. **Admin Login**: https://go.habibistay.com/admin/login
3. **API Status**: https://go.habibistay.com/api/status

---

## 🛠️ Troubleshooting

If you encounter any issues:

### Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

### Test database connection:
```bash
php artisan tinker
# Then run: DB::connection()->getPdo();
```

### Verify environment:
```bash
cat .env | grep DB_
```

---

## ✅ Success Indicators

- ✅ All commands execute without errors
- ✅ Homepage loads at https://go.habibistay.com
- ✅ Admin panel accessible
- ✅ No errors in Laravel logs

---

**🎉 Your HabibiStay application will be live after completing these steps!**
