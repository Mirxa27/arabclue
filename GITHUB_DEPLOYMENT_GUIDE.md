# HabibiStay - Full GitHub Deployment Guide

## 🚀 Overview

This guide covers the complete GitHub Actions deployment workflow for HabibiStay, featuring automated testing, deployment, monitoring, and notifications.

## 📋 Features

### 🔧 Full Deployment Workflow (`deploy.yml`)

- **Automated Testing**: Runs PHPUnit tests before deployment
- **Asset Compilation**: Builds production-ready CSS/JS assets
- **Database Management**: Runs migrations and seeders
- **Performance Optimization**: Caches configs, routes, and views
- **Security Hardening**: Sets proper permissions and headers
- **Health Monitoring**: Post-deployment health checks
- **Backup Management**: Automatic backups with retention
- **Maintenance Mode**: Zero-downtime deployments
- **Slack Notifications**: Success/failure notifications
- **Performance Testing**: Load time monitoring

### ✨ Key Capabilities

1. **Smart Deployment Options**:
   - Manual deployment with custom parameters
   - Automatic deployment on push to main
   - Deployment on merged pull requests

2. **Advanced Configuration**:
   - Environment selection (production/staging)
   - Optional migration control
   - Cache clearing options
   - Feature flag management

3. **Comprehensive Testing**:
   - Unit and feature tests
   - Database connection testing
   - API endpoint validation
   - Performance benchmarking

4. **Enterprise Features**:
   - Sara AI integration
   - Advanced analytics
   - Email templates
   - Booking system
   - Payment gateways
   - Mobile app support
   - Admin/host dashboards

## 🛠️ Setup Instructions

### 1. Create GitHub Repository

```bash
# Create repository on GitHub (name: habibistay)
# Then run locally:
git commit -m 'Initial commit: HabibiStay Laravel application'
git remote add origin https://github.com/YOUR_USERNAME/habibistay.git
git push -u origin main
```

### 2. Configure GitHub Secrets

Go to Repository Settings > Secrets and variables > Actions

#### Required Secrets:
```
SSH_PASSWORD=Mirxa420$
APP_KEY=base64:giVpUi4zqfBeye9gvMfpcJBvKAIu6Z6YAJTxzk2Et98=
DB_PASSWORD=Mirxa420$
```

#### Optional Secrets (for full functionality):
```
MAINTENANCE_SECRET=your-secret-key-for-maintenance-mode
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MYFATOORAH_API_KEY=your-myfatoorah-key
PAYPAL_SANDBOX_CLIENT_ID=your-paypal-sandbox-id
PAYPAL_SANDBOX_CLIENT_SECRET=your-paypal-sandbox-secret
PAYPAL_LIVE_CLIENT_ID=your-paypal-live-id
PAYPAL_LIVE_CLIENT_SECRET=your-paypal-live-secret
OPENAI_API_KEY=your-openai-api-key
```

### 3. Environment Variables Configured

The deployment automatically sets up:

#### Core Laravel Settings:
- Production environment with debugging disabled
- Database connection to Hostinger MySQL
- Optimized caching and session settings

#### Sara AI Configuration:
- Voice and chat capabilities enabled
- GPT-4 and Whisper-1 models configured
- Multi-language support (English/Arabic)

#### Feature Flags:
- All HabibiStay features enabled by default
- Booking system, payments, notifications
- Admin and host dashboards
- Advanced search and analytics

#### Security Settings:
- Enhanced CSP headers
- XSS and CSRF protection
- Secure file permissions
- HTTPS enforcement

#### Performance Optimizations:
- Asset compression and caching
- Database query optimization
- File expiration headers
- Code optimization

## 🎯 Deployment Workflows

### Automatic Deployment
- **Trigger**: Push to `main` branch
- **Process**: Test → Build → Deploy → Monitor
- **Duration**: ~5-10 minutes

### Manual Deployment
1. Go to repository > Actions
2. Select "Full Deploy to Hostinger with All Features"
3. Click "Run workflow"
4. Choose options:
   - Environment: production/staging
   - Run migrations: true/false
   - Clear cache: true/false

### Pull Request Deployment
- **Trigger**: Merged pull request
- **Process**: Runs tests first, then deploys if merged

## 📊 Deployment Process

### Phase 1: Testing (2-3 minutes)
- Setup PHP 8.1 and Node.js 18
- Install dependencies
- Run PHPUnit tests with coverage
- Build production assets

### Phase 2: Deployment (3-5 minutes)
- Create automatic backup
- Enable maintenance mode
- Clone repository to server
- Install dependencies
- Run database migrations
- Clear and rebuild caches
- Set proper permissions
- Disable maintenance mode

### Phase 3: Monitoring (5 minutes)
- Health checks on main site
- Admin panel accessibility test
- API endpoint validation
- Performance testing
- Stability monitoring

## 🔧 Advanced Features

### Backup Management
- Automatic backups before each deployment
- Retention of last 5 backups
- Excludes logs and cache files

### Zero-Downtime Deployment
- Maintenance mode with custom page
- Secret bypass for testing
- Graceful error handling

### Performance Monitoring
- Page load time tracking
- HTTP status monitoring
- Database connection testing
- API health checks

### Notification System
- Slack notifications for success/failure
- Detailed deployment information
- Performance metrics
- Error reporting

## 🌍 Production URLs

After successful deployment:

- **Main Site**: https://go.habibistay.com
- **Admin Panel**: https://go.habibistay.com/admin
- **API Health**: https://go.habibistay.com/api/health

## 🐛 Troubleshooting

### Common Issues:

1. **SSH Connection Failed**
   - Check SSH_PASSWORD secret
   - Verify Hostinger server access

2. **Database Migration Failed**
   - Check DB_PASSWORD secret
   - Verify database credentials

3. **Asset Build Failed**
   - Check package.json dependencies
   - Verify Node.js compatibility

4. **Permission Errors**
   - Check file ownership on server
   - Verify directory permissions

### Debug Steps:

1. Check GitHub Actions logs
2. SSH to server and check Laravel logs:
   ```bash
   tail -f /home/u221943340/domains/go.habibistay.com/public_html/storage/logs/laravel.log
   ```
3. Check deployment log:
   ```bash
   tail -f /home/u221943340/deployment.log
   ```

## 📈 Monitoring and Maintenance

### Automatic Monitoring
- Post-deployment health checks
- 5-minute stability monitoring
- Performance benchmarking
- Error rate tracking

### Manual Monitoring
- Check site accessibility
- Monitor server resources
- Review error logs
- Performance optimization

## 🔒 Security Features

### Enhanced Security Headers
- Content Security Policy (CSP)
- X-Frame-Options protection
- XSS protection headers
- Referrer policy configuration

### File Protection
- .env file access restriction
- Sensitive file permissions
- Directory traversal protection
- Input sanitization

## 🚀 Next Steps

1. **Monitor Performance**: Use deployment metrics
2. **Scale Resources**: Monitor server usage
3. **Optimize Code**: Based on performance data
4. **Enhance Security**: Regular security audits
5. **Feature Development**: Add new capabilities

---

**Deployment Status**: Ready for production
**Last Updated**: $(date)
**Version**: 1.0.0