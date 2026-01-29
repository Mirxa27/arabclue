#!/bin/bash

# Simple upload script for the deployment package

echo "🚀 HabibiStay Package Upload"
echo "============================"

PACKAGE_FILE="habibistay-clean-deploy-20250609_075746.tar.gz"
HOST="195.35.57.85"
PORT="65002"
USER="u221943340"

if [ ! -f "$PACKAGE_FILE" ]; then
    echo "❌ Package file not found: $PACKAGE_FILE"
    echo "Please make sure the deployment package exists."
    exit 1
fi

echo "📦 Package: $PACKAGE_FILE"
echo "🖥️  Server: $USER@$HOST:$PORT"
echo "📁 Target: /home/$USER/"
echo ""
echo "🔑 You will be prompted for the password: Mirxa420$"
echo ""

# Upload the package
echo "📤 Uploading package..."
scp -P $PORT "$PACKAGE_FILE" "$USER@$HOST:/home/$USER/"

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Package uploaded successfully!"
    echo ""
    echo "🔗 Next steps:"
    echo "1. Connect to server: ssh -p $PORT $USER@$HOST"
    echo "2. Follow the deployment commands in MANUAL_CLEAN_DEPLOYMENT.md"
    echo ""
else
    echo ""
    echo "❌ Upload failed!"
    echo ""
    echo "💡 Alternative options:"
    echo "1. Use Hostinger File Manager to upload the package"
    echo "2. Check your internet connection and try again"
    echo "3. Verify server credentials"
    echo ""
fi
