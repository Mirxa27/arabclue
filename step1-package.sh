#!/bin/bash

# HabibiStay Manual Deployment Steps
# Run each command separately for better control

echo "Step 1: Create deployment package"
tar -czf habibistay-deploy.tar.gz \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='.env.local' \
    --exclude='*.log' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='vendor' \
    --exclude='*.zip' \
    --exclude='*.tar.gz' \
    .

echo "✅ Package created: habibistay-deploy.tar.gz"
ls -lh habibistay-deploy.tar.gz
