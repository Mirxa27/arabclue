#!/bin/bash

# Step 2: Upload to server
echo "Step 2: Uploading to Hostinger server..."

# Server details
HOST="195.35.57.85"
PORT="65002"
USER="u221943340"
PASS="Mirxa420$"

# Upload the package
scp -P $PORT habibistay-deploy.tar.gz $USER@$HOST:/home/$USER/

echo "✅ Upload completed"
