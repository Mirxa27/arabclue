#!/bin/bash

echo "🚀 HabibiStay - Deployment Readiness Test"
echo "=================================================="
echo ""

BASE_URL="http://127.0.0.1:8002"
PASSED=0
FAILED=0

# Function to test endpoint
test_endpoint() {
    local url="$1"
    local expected_status="$2"
    local description="$3"
    
    local status=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    
    if [ "$status" = "$expected_status" ]; then
        echo "✅ $description: HTTP $status"
        ((PASSED++))
    else
        echo "❌ $description: Expected HTTP $expected_status, got HTTP $status"
        ((FAILED++))
    fi
}

echo "1. 🏠 Core Application Routes"
echo "-------------------------------"
test_endpoint "$BASE_URL/" 200 "Home page"
test_endpoint "$BASE_URL/stays" 200 "Properties listing page"
test_endpoint "$BASE_URL/sara" 200 "Sara chat page"
test_endpoint "$BASE_URL/about" 200 "About page"
test_endpoint "$BASE_URL/contact" 200 "Contact page"
test_endpoint "$BASE_URL/terms" 200 "Terms page"
test_endpoint "$BASE_URL/privacy" 200 "Privacy page"
echo ""

echo "2. 🔧 API Health and Configuration"
echo "-------------------------------"
test_endpoint "$BASE_URL/api/health" 200 "API Health endpoint"
test_endpoint "$BASE_URL/api/v1/config" 200 "API Config endpoint"
echo ""

echo "3. 🔐 Authentication Routes"
echo "-------------------------------"
test_endpoint "$BASE_URL/login" 200 "Login page"
test_endpoint "$BASE_URL/register" 200 "Register page"
echo ""

echo "4. 👑 Admin Dashboard Routes"
echo "-------------------------------"
test_endpoint "$BASE_URL/admin-test" 200 "Admin test page"
test_endpoint "$BASE_URL/admin" 200 "Admin dashboard"
echo ""

echo "5. 🏡 Host Dashboard Routes"
echo "-------------------------------"
test_endpoint "$BASE_URL/host-test" 200 "Host test page"
echo ""

echo "6. 📧 Email Preview Routes"
echo "-------------------------------"
test_endpoint "$BASE_URL/email-preview" 200 "Email preview index"
test_endpoint "$BASE_URL/email-preview/welcome" 200 "Welcome email preview"
test_endpoint "$BASE_URL/email-preview/booking-reminder-checkin" 200 "Booking reminder email"
echo ""

echo "7. 🔍 Search and Property Routes"
echo "-------------------------------"
test_endpoint "$BASE_URL/search" 200 "Search page"
test_endpoint "$BASE_URL/api/v1/search/cities" 200 "Cities API"
test_endpoint "$BASE_URL/api/v1/amenities" 200 "Amenities API"
echo ""

echo "8. ⚠️ Error Handling"
echo "-------------------------------"
test_endpoint "$BASE_URL/non-existent-route" 404 "404 Error handling"
echo ""

echo "9. 📊 Database Connectivity"
echo "-------------------------------"
# Test database through API
status=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/api/v1/properties/search")
if [ "$status" = "200" ] || [ "$status" = "422" ]; then
    echo "✅ Database connectivity: Working (HTTP $status)"
    ((PASSED++))
else
    echo "❌ Database connectivity: Failed (HTTP $status)"
    ((FAILED++))
fi
echo ""

echo "10. 🗂️ Static Files"
echo "-------------------------------"
# Check if key files exist
if [ -f "public/manifest.json" ]; then
    echo "✅ PWA Manifest: Found"
    ((PASSED++))
else
    echo "❌ PWA Manifest: Missing"
    ((FAILED++))
fi

if [ -f "public/service-worker.js" ]; then
    echo "✅ Service Worker: Found"
    ((PASSED++))
else
    echo "❌ Service Worker: Missing"
    ((FAILED++))
fi
echo ""

# Final Summary
echo "📊 DEPLOYMENT READINESS SUMMARY"
echo "=================================================="
echo "✅ Tests Passed: $PASSED"
echo "❌ Tests Failed: $FAILED"

if [ $FAILED -eq 0 ]; then
    echo "🎉 EXCELLENT! Your application is ready for deployment!"
    exit 0
elif [ $FAILED -le 3 ]; then
    echo "⚠️ GOOD! Minor issues found. Review failed tests before deployment."
    exit 1
else
    echo "🚨 ATTENTION NEEDED! Multiple issues found. Address failed tests before deployment."
    exit 2
fi
