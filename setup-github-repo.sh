#!/bin/bash

echo "========================================="
echo "GITHUB REPOSITORY SETUP FOR HABIBISTAY"
echo "========================================="
echo ""

# Check if git is initialized
if [ ! -d ".git" ]; then
    echo "Initializing Git repository..."
    git init
    git branch -M main
else
    echo "Git repository already initialized"
fi

# Clean up deployment files
echo "Cleaning up deployment files..."
rm -f *.tar.gz *.exp deploy*.sh hostinger*.sh emergency*.sh fix*.sh manual*.sh quick*.sh auto*.sh
rm -f .htaccess-* .env.production env-*
rm -f *.sql debug-*.php test-*.php create-*.php verify-*.php run-*.php check-*.php

# Add files to git
echo "Adding files to Git..."
git add .
git status

echo ""
echo "========================================="
echo "NEXT STEPS:"
echo "========================================="
echo ""
echo "1. Create a new repository on GitHub:"
echo "   - Go to https://github.com/new"
echo "   - Name: habibistay"
echo "   - Make it private or public"
echo "   - Don't initialize with README (we already have files)"
echo ""
echo "2. After creating the repository, run these commands:"
echo ""
echo "   git commit -m 'Initial commit: HabibiStay Laravel application'"
echo "   git remote add origin https://github.com/YOUR_USERNAME/habibistay.git"
echo "   git push -u origin main"
echo ""
echo "3. Set up GitHub Secrets (go to Repository Settings > Secrets and variables > Actions):"
echo "   Required secrets:"
echo "   - SSH_PASSWORD: Mirxa420$"
echo "   - APP_KEY: base64:giVpUi4zqfBeye9gvMfpcJBvKAIu6Z6YAJTxzk2Et98="
echo "   - DB_PASSWORD: Mirxa420$"
echo ""
echo "   Optional secrets (for full functionality):"
echo "   - MAIL_USERNAME: your-email@gmail.com"
echo "   - MAIL_PASSWORD: your-app-password"
echo "   - MYFATOORAH_API_KEY: your-api-key"
echo "   - PAYPAL_SANDBOX_CLIENT_ID: your-paypal-id"
echo "   - PAYPAL_SANDBOX_CLIENT_SECRET: your-paypal-secret"
echo "   - OPENAI_API_KEY: your-openai-key"
echo ""
echo "4. GitHub Actions will automatically deploy when you push to main branch"
echo ""
echo "========================================="
echo "MANUAL DEPLOYMENT (if needed):"
echo "========================================="
echo ""
echo "Go to your GitHub repository > Actions > Simple Deploy to Hostinger > Run workflow"
echo ""
echo "Your site will be available at: https://go.habibistay.com"