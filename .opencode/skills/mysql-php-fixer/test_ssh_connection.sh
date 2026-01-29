#!/bin/bash

# SSH MySQL Test Script for arabclue.com
# Tests connection via SSH and performs database operations

# Configuration
SSH_HOST="147.93.48.177"
SSH_PORT="65002"
SSH_USER="u726786619"
SSH_PASSWORD="Mirxa420$"

DB_HOST="srv1513.hstgr.io"
DB_PORT="3306"
DB_NAME="u726786619_arab_db"
DB_USER="u726786619_arab_db"
DB_PASSWORD="Mirxa420$"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}=== SSH MySQL Connection Test for arabclue.com ===${NC}"

# Test SSH connection
echo -e "\n${BLUE}Testing SSH connection...${NC}"
if sshpass -p "$SSH_PASSWORD" ssh -p "$SSH_PORT" -o StrictHostKeyChecking=no -o ConnectTimeout=10 "$SSH_USER@$SSH_HOST" "echo 'SSH connection successful' && whoami && hostname" > /dev/null 2>&1; then
    echo -e "${GREEN}✅ SSH connection successful${NC}"
else
    echo -e "${RED}❌ SSH connection failed${NC}"
    exit 1
fi

# Test MySQL connection via SSH
echo -e "\n${BLUE}Testing MySQL connection via SSH...${NC}"
if sshpass -p "$SSH_PASSWORD" ssh -p "$SSH_PORT" -o StrictHostKeyChecking=no "$SSH_USER@$SSH_HOST" "mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p'$DB_PASSWORD' $DB_NAME -e 'SELECT 1;' > /dev/null 2>&1"; then
    echo -e "${GREEN}✅ MySQL connection successful${NC}"
else
    echo -e "${RED}❌ MySQL connection failed${NC}"
    exit 1
fi

echo -e "\n${GREEN}=== All tests passed! ===${NC}"