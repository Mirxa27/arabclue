#!/bin/bash

# Quick Deployment Test Script
# Tests if HabibiStay is properly deployed and accessible

echo "🔍 HABIBI STAY - DEPLOYMENT VERIFICATION"
echo "========================================"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

test_url() {
    local url=$1
    local description=$2
    
    echo -n "Testing $description... "
    
    if curl -s -f -L "$url" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ PASS${NC}"
        return 0
    else
        echo -e "${RED}❌ FAIL${NC}"
        return 1
    fi
}

echo "Testing website accessibility..."
echo ""

# Test main pages
test_url "https://go.habibistay.com" "Homepage"
test_url "https://go.habibistay.com/login" "Login Page"
test_url "https://go.habibistay.com/register" "Registration Page"
test_url "https://go.habibistay.com/properties" "Properties Page"
test_url "https://go.habibistay.com/admin" "Admin Panel"

echo ""
echo "Testing API endpoints..."

# Test API endpoints
test_url "https://go.habibistay.com/api/health" "API Health Check"
test_url "https://go.habibistay.com/api/v1/config" "API Configuration"

echo ""
echo "🔍 Manual Testing URLs:"
echo "• Homepage: https://go.habibistay.com"
echo "• Admin Panel: https://go.habibistay.com/admin"
echo "• API Health: https://go.habibistay.com/api/health"
echo "• Properties: https://go.habibistay.com/properties"
echo ""
echo "📧 Default Admin Credentials:"
echo "• Email: admin@habibistay.com"
echo "• Password: admin123"
echo ""

# Check if deployment was successful
if test_url "https://go.habibistay.com" "Final Check" > /dev/null 2>&1; then
    echo -e "${GREEN}🎉 DEPLOYMENT SUCCESSFUL!${NC}"
    echo "Your HabibiStay platform is live and accessible."
else
    echo -e "${YELLOW}⚠️  DEPLOYMENT NEEDS ATTENTION${NC}"
    echo "The website may not be fully accessible. Please check server configuration."
fi
