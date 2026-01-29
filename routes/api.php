<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SaraChatbotController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\MessagingController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\SaraVoiceController;
use App\Http\Controllers\Api\MobileAppController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PropertyController as AdminPropertyController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\ConfigurationController;
use App\Http\Controllers\Api\iCalController;
use App\Http\Controllers\Api\DisputeController;
use App\Http\Controllers\Api\UserPreferenceController;
use App\Http\Controllers\Api\UserActivityController;

/*
|--------------------------------------------------------------------------
| API Routes - RESTful API Endpoints
|--------------------------------------------------------------------------
*/

// API Version 1
Route::prefix('v1')->group(function () {
    
    // Public Authentication Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/social-login', [AuthController::class, 'socialLogin']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    
    // Advanced Search Routes
    Route::get('/advanced-search', [\App\Http\Controllers\Api\AdvancedSearchController::class, 'search']);
    Route::get('/search-filters', [\App\Http\Controllers\Api\AdvancedSearchController::class, 'getFilterOptions']);
    
    // Public Property Routes
    Route::prefix('properties')->group(function () {
        Route::get('/featured', [PropertyController::class, 'featured']);
        Route::get('/search', [PropertyController::class, 'search']);
        Route::get('/{slug}', [PropertyController::class, 'show']);
        Route::get('/{property}/availability', [PropertyController::class, 'availability']);
        Route::post('/{property}/calculate-price', [PropertyController::class, 'calculatePrice']);
        Route::get('/{property}/reviews', [PropertyController::class, 'reviews']);
        Route::get('/{property}/nearby', [PropertyController::class, 'nearby']);
        Route::get('/{property}/ical-export', [iCalController::class, 'export']);
        
        // Host-only property management
        Route::middleware(['auth:sanctum', 'host'])->group(function () {
            Route::post('/', [PropertyController::class, 'store']);
            Route::put('/{property}', [PropertyController::class, 'update']);
            Route::delete('/{property}', [PropertyController::class, 'destroy']);
            Route::post('/{property}/images', [PropertyController::class, 'uploadImages']);
            Route::put('/{property}/amenities', [PropertyController::class, 'updateAmenities']);
        });
    });
    
    // Sara Chatbot Routes (Public)
    Route::prefix('sara')->group(function () {
        Route::post('/start', [SaraChatbotController::class, 'start']);
        Route::post('/message', [SaraChatbotController::class, 'message']);
        Route::post('/action', [SaraChatbotController::class, 'handleAction']);
        Route::get('/conversations/{conversation}/history', [SaraChatbotController::class, 'getHistory']);
        Route::post('/conversations/{conversation}/end', [SaraChatbotController::class, 'endConversation']);
        Route::get('/conversations/{conversation}/suggestions', [SaraChatbotController::class, 'getSuggestions']);
        Route::post('/feedback', [SaraChatbotController::class, 'sendFeedback']);
    });
    
    // Search & Filters
    Route::get('/search/suggestions', [SearchController::class, 'suggestions']);
    Route::get('/search/cities', [SearchController::class, 'cities']);
    Route::get('/search/neighborhoods/{city}', [SearchController::class, 'neighborhoods']);
    Route::get('/amenities', [SearchController::class, 'amenities']);
    
    // Payment Webhooks & Callbacks (Public)
    Route::prefix('payments')->group(function () {
        Route::post('/webhooks/paypal', [PaymentController::class, 'paypalWebhook']);
        Route::post('/webhooks/myfatoorah', [PaymentController::class, 'myfatoorahWebhook']);
        Route::get('/paypal/success/{booking}', [PaymentController::class, 'paypalSuccess'])->name('payments.paypal.success');
        Route::get('/paypal/cancel/{booking}', [PaymentController::class, 'paypalCancel'])->name('payments.paypal.cancel');
        Route::get('/myfatoorah/callback/{booking}', [PaymentController::class, 'myfatoorahCallback'])->name('payments.myfatoorah.callback');
        Route::get('/myfatoorah/error/{booking}', [PaymentController::class, 'myfatoorahError'])->name('payments.myfatoorah.error');
    });
    
    // Authenticated Routes
    Route::middleware('auth:sanctum')->group(function () {
        
        // User Profile
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('/', [ProfileController::class, 'update']);
            Route::post('/change-password', [ProfileController::class, 'changePassword']);
            Route::post('/avatar', [ProfileController::class, 'updateAvatar']);
            Route::post('/verify-identity', [ProfileController::class, 'verifyIdentity']);
            Route::put('/preferences', [ProfileController::class, 'updatePreferences']);
            Route::put('/notification-settings', [ProfileController::class, 'updateNotificationSettings']);
            Route::post('/update-device', [ProfileController::class, 'updateDevice']);
            Route::delete('/', [ProfileController::class, 'delete']);
        });
        
        // User Preferences Management
        Route::prefix('user')->group(function () {
            Route::prefix('preferences')->group(function () {
                Route::get('/', [UserPreferenceController::class, 'index']);
                Route::post('/', [UserPreferenceController::class, 'store']);
                Route::put('/{preference}', [UserPreferenceController::class, 'update']);
                Route::delete('/{preference}', [UserPreferenceController::class, 'destroy']);
                Route::get('/search', [UserPreferenceController::class, 'search']);
                Route::post('/bulk-update', [UserPreferenceController::class, 'bulkUpdate']);
            });
            
            Route::prefix('activities')->group(function () {
                Route::get('/', [UserActivityController::class, 'index']);
                Route::post('/', [UserActivityController::class, 'store']);
                Route::get('/recent', [UserActivityController::class, 'recent']);
                Route::get('/stats', [UserActivityController::class, 'stats']);
                Route::delete('/{activity}', [UserActivityController::class, 'destroy']);
                Route::post('/bulk-delete', [UserActivityController::class, 'bulkDelete']);
            });
        });
        
        // Referral System
        Route::prefix('referrals')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\ReferralController::class, 'myReferrals']);
            Route::post('/generate-code', [\App\Http\Controllers\Api\ReferralController::class, 'generateNewCode']);
            Route::post('/send-invites', [\App\Http\Controllers\Api\ReferralController::class, 'sendInvites']);
            Route::post('/apply-credits', [\App\Http\Controllers\Api\ReferralController::class, 'applyCreditsToBooking']);
        });
        
        // Wishlist & Collections
        Route::prefix('wishlists')->group(function () {
            // Wishlist Collections management
            Route::get('/collections', [WishlistController::class, 'getCollections']);
            Route::post('/collections', [WishlistController::class, 'createCollection']);
            Route::put('/collections/{id}', [WishlistController::class, 'updateCollection']);
            Route::delete('/collections/{id}', [WishlistController::class, 'deleteCollection']);
            
            // Wishlisted properties management
            Route::get('/', [WishlistController::class, 'index']);
            Route::post('/add-to-collection', [WishlistController::class, 'addToCollection']);
            Route::post('/{propertyId}/toggle', [WishlistController::class, 'toggle']);
            Route::put('/{id}', [WishlistController::class, 'update']);
            Route::delete('/{id}', [WishlistController::class, 'remove']);
        });
        
        // Bookings
        Route::prefix('bookings')->group(function () {
            Route::get('/', [BookingController::class, 'index']);
            Route::post('/', [BookingController::class, 'create']);
            Route::get('/{booking}', [BookingController::class, 'show']);
            Route::put('/{booking}/cancel', [BookingController::class, 'cancel']);
            Route::post('/{booking}/review', [BookingController::class, 'submitReview']);
            Route::get('/{booking}/invoice', [BookingController::class, 'invoice']);
        });
        
        // Messages & Conversations
        Route::prefix('conversations')->group(function () {
            Route::get('/', [MessagingController::class, 'getConversations']);
            Route::post('/send-message', [MessagingController::class, 'sendMessage']);
            Route::post('/support', [MessagingController::class, 'createSupportConversation']);
            Route::get('/{conversation}/messages', [MessagingController::class, 'getMessages']);
            Route::post('/{conversation}/mark-read', [MessagingController::class, 'markAsRead']);
            Route::post('/{conversation}/archive', [MessagingController::class, 'archiveConversation']);
            Route::get('/{conversation}/participants', [MessagingController::class, 'getParticipants']);
            Route::post('/{conversation}/booking-response', [MessagingController::class, 'sendBookingResponse']);
        });
        
        Route::prefix('messages')->group(function () {
            Route::post('/search', [MessagingController::class, 'searchMessages']);
            Route::put('/{message}/edit', [MessagingController::class, 'editMessage']);
            Route::delete('/{message}', [MessagingController::class, 'deleteMessage']);
            Route::post('/booking-inquiry', [MessagingController::class, 'sendBookingInquiry']);
            Route::post('/set-online-status', [MessagingController::class, 'setOnlineStatus']);
            Route::get('/stats', [MessagingController::class, 'getStats']);
            Route::post('/toggle-block', [MessagingController::class, 'toggleUserBlock']);
        });
        
        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [ProfileController::class, 'notifications']);
            Route::put('/{notification}/read', [ProfileController::class, 'markNotificationRead']);
            Route::put('/mark-all-read', [ProfileController::class, 'markAllNotificationsRead']);
            Route::delete('/{notification}', [ProfileController::class, 'deleteNotification']);
        });
        
        // Dispute Management
        Route::prefix('disputes')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\DisputeController::class, 'index']);
            Route::post('/booking/{booking}', [App\Http\Controllers\Api\DisputeController::class, 'store']);
            Route::get('/{dispute}', [App\Http\Controllers\Api\DisputeController::class, 'show']);
            Route::post('/{dispute}/reply', [App\Http\Controllers\Api\DisputeController::class, 'reply']);
            Route::post('/{dispute}/resolve', [App\Http\Controllers\Api\DisputeController::class, 'resolve'])->middleware('admin');
        });
        
        // Host Routes
        Route::middleware('host')->prefix('host')->group(function () {
            // Dashboard
            Route::get('/dashboard', [App\Http\Controllers\Api\HostController::class, 'dashboard']);

            // Properties
            Route::get('/properties', [App\Http\Controllers\Api\HostController::class, 'properties']);
            Route::post('/properties', [App\Http\Controllers\Api\HostController::class, 'createProperty']);
            Route::get('/properties/sync-status', [App\Http\Controllers\Api\HostController::class, 'getPropertySyncStatus']);
            Route::post('/properties/{property}/sync', [App\Http\Controllers\Api\HostController::class, 'syncProperty']);
            Route::post('/properties/{property}/toggle-featured', [App\Http\Controllers\Api\HostController::class, 'toggleFeatured']);
            Route::put('/properties/{property}/pricing', [App\Http\Controllers\Api\HostController::class, 'updatePricing']);

            // Calendar & iCal
            Route::post('properties/{property}/ical-import', [iCalController::class, 'import']);
            Route::get('properties/{property}/ical-export', [iCalController::class, 'export'])->name('api.properties.ical-export');
            Route::post('properties/{property}/ical-feeds', [iCalController::class, 'manageFeeds']);
            Route::get('properties/{property}/ical-feeds', [iCalController::class, 'getFeeds']);
            Route::post('properties/{property}/ical-sync', [iCalController::class, 'syncAll']);

            // Channel Manager
            Route::prefix('channel-manager')->group(function () {
                Route::get('/channels', [App\Http\Controllers\Api\ChannelManagerController::class, 'index']);
                Route::post('/channels', [App\Http\Controllers\Api\ChannelManagerController::class, 'store']);
                Route::post('/channels/sync-all', [App\Http\Controllers\Api\ChannelManagerController::class, 'syncAll']);
                Route::post('/channels/{channel}/sync', [App\Http\Controllers\Api\ChannelManagerController::class, 'syncChannel']);
                Route::post('/channels/{channel}/sync-calendars', [App\Http\Controllers\Api\ChannelManagerController::class, 'syncChannelCalendars']);
                Route::post('/sync-calendars', [App\Http\Controllers\Api\ChannelManagerController::class, 'syncAllCalendars']);
                Route::get('/financial-summary', [App\Http\Controllers\Api\ChannelManagerController::class, 'getFinancialSummary']);
                Route::get('/sync-logs', [App\Http\Controllers\Api\ChannelManagerController::class, 'getSyncLogs']);
                Route::get('/available-channels', [App\Http\Controllers\Api\ChannelManagerController::class, 'getAvailableChannels']);
            });

            // Financial Reports
            Route::get('/financial-report', [App\Http\Controllers\Api\HostController::class, 'financialReport']);
            Route::put('/properties/{property}', [App\Http\Controllers\Api\HostController::class, 'updateProperty']);
            Route::delete('/properties/{property}', [App\Http\Controllers\Api\HostController::class, 'deleteProperty']);
            Route::get('/bookings', [App\Http\Controllers\Api\HostController::class, 'bookings']);
            Route::put('/bookings/{booking}/accept', [App\Http\Controllers\Api\HostController::class, 'acceptBooking']);
            Route::put('/bookings/{booking}/decline', [App\Http\Controllers\Api\HostController::class, 'declineBooking']);
            Route::get('/earnings', [App\Http\Controllers\Api\HostController::class, 'earnings']);
            Route::get('/analytics', [App\Http\Controllers\Api\HostController::class, 'analytics']);
            Route::get('/transactions', [App\Http\Controllers\Api\HostController::class, 'transactionHistory']);
            Route::get('/transactions/export', [App\Http\Controllers\Api\HostController::class, 'exportTransactions']);
        });
        
        // Payment Routes
        Route::prefix('payments')->group(function () {
            Route::post('/bookings/{booking}/intent', [PaymentController::class, 'createPaymentIntent']);
            Route::post('/bookings/{booking}/process', [PaymentController::class, 'processPayment']);
            Route::get('/bookings/{booking}/methods', [PaymentController::class, 'getPaymentMethods']);
            Route::get('/bookings/{booking}/status', [PaymentController::class, 'getPaymentStatus']);
            Route::post('/calculate-fees', [PaymentController::class, 'calculateFees']);
        });
        
        // Logout
        // Wishlist
        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
        Route::delete('/wishlist/{wishlist}', [WishlistController::class, 'destroy']);

        Route::post('/logout', [AuthController::class, 'logout']);
    });

    
    // Admin Routes
    Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
        // Dashboard
        Route::get('/dashboard-stats', [\App\Http\Controllers\Admin\DashboardController::class, 'getStats']);

        // Admin Dashboard APIs
        Route::prefix('dashboard')->group(function () {
            Route::get('/overview', [\App\Http\Controllers\Admin\DashboardApiController::class, 'getOverview']);
            Route::get('/system-status', [\App\Http\Controllers\Admin\DashboardApiController::class, 'getSystemStatus']);
            Route::get('/sara-stats', [\App\Http\Controllers\Admin\DashboardApiController::class, 'getSaraStats']);
            Route::get('/referral-stats', [\App\Http\Controllers\Admin\DashboardApiController::class, 'getReferralStats']);
            Route::get('/map-data', [\App\Http\Controllers\Admin\DashboardApiController::class, 'getMapData']);
            Route::get('/health-check', [\App\Http\Controllers\Admin\DashboardApiController::class, 'getHealthCheck']);
        });

        // User Management
        Route::apiResource('users', AdminUserController::class)->names([
            'index' => 'admin.api.users.index',
            'show' => 'admin.api.users.show',
            'store' => 'admin.api.users.store',
            'update' => 'admin.api.users.update',
            'destroy' => 'admin.api.users.destroy'
        ]);
        Route::get('/users/export', [AdminUserController::class, 'export']);

        // Property Management
        Route::apiResource('properties', AdminPropertyController::class)->except(['store'])->names([
            'index' => 'admin.api.properties.index',
            'show' => 'admin.api.properties.show',
            'update' => 'admin.api.properties.update',
            'destroy' => 'admin.api.properties.destroy'
        ]);

        // Booking Management
        Route::apiResource('bookings', AdminBookingController::class)->except(['store'])->names([
            'index' => 'admin.api.bookings.index',
            'show' => 'admin.api.bookings.show',
            'update' => 'admin.api.bookings.update',
            'destroy' => 'admin.api.bookings.destroy'
        ]);

        // Coupon Management
        Route::apiResource('coupons', AdminCouponController::class)->names([
            'index' => 'admin.api.coupons.index',
            'show' => 'admin.api.coupons.show',
            'store' => 'admin.api.coupons.store',
            'update' => 'admin.api.coupons.update',
            'destroy' => 'admin.api.coupons.destroy'
        ]);

        // Sara AI Configuration
        Route::get('/sara/config', [\App\Http\Controllers\Admin\SaraConfigController::class, 'getConfig']);
        Route::post('/sara/config', [\App\Http\Controllers\Admin\SaraConfigController::class, 'updateConfig']);
        Route::post('/sara/test', [\App\Http\Controllers\Admin\SaraConfigController::class, 'testMessage']);
        Route::get('/sara/stats', [\App\Http\Controllers\Admin\SaraConfigController::class, 'getUsageStats']);
        Route::post('/sara/reset', [\App\Http\Controllers\Admin\SaraConfigController::class, 'resetConfig']);
        Route::get('/sara/export', [\App\Http\Controllers\Admin\SaraConfigController::class, 'exportConfig']);
        Route::post('/sara/import', [\App\Http\Controllers\Admin\SaraConfigController::class, 'importConfig']);
        Route::post('/sara/config', [\App\Http\Controllers\Admin\SaraConfigController::class, 'updateConfig']);
        Route::post('/sara/test-message', [\App\Http\Controllers\Admin\SaraConfigController::class, 'testMessage']);

        Route::prefix('referrals')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReferralController::class, 'index']);
            Route::get('/{referral}', [\App\Http\Controllers\Admin\ReferralController::class, 'show']);
            Route::get('/settings', [\App\Http\Controllers\Admin\ReferralController::class, 'getSettings']);
            Route::post('/settings', [\App\Http\Controllers\Admin\ReferralController::class, 'updateSettings']);
        });

        Route::prefix('content')->group(function () {
            Route::get('/featured-cities', [\App\Http\Controllers\Admin\ContentController::class, 'getFeaturedCities']);
            Route::post('/featured-cities', [\App\Http\Controllers\Admin\ContentController::class, 'updateFeaturedCities']);
            Route::get('/sliders', [\App\Http\Controllers\Admin\ContentController::class, 'getSliders']);
            Route::post('/sliders', [\App\Http\Controllers\Admin\ContentController::class, 'updateSliders']);
        });

        // Enhanced Settings Management
        Route::prefix('settings')->group(function () {
            Route::get('/env', [\App\Http\Controllers\Admin\SaraConfigController::class, 'getEnvVariables']);
            Route::post('/env', [\App\Http\Controllers\Admin\SaraConfigController::class, 'updateEnvVariables']);
            Route::get('/currencies', [\App\Http\Controllers\Admin\SettingsController::class, 'getCurrencies']);
            Route::post('/currencies', [\App\Http\Controllers\Admin\SettingsController::class, 'updateCurrencies']);
            Route::get('/languages', [\App\Http\Controllers\Admin\SettingsController::class, 'getLanguages']);
            Route::post('/languages', [\App\Http\Controllers\Admin\SettingsController::class, 'updateLanguages']);
        });
    });

    // Dispute Routes
    Route::middleware('auth:sanctum')->prefix('disputes')->group(function () {
        Route::post('/{booking}', [DisputeController::class, 'store']);
        Route::get('/{dispute}', [DisputeController::class, 'show']);
        Route::post('/{dispute}/reply', [DisputeController::class, 'reply']);
    });
    
    // Enhanced User Profile Routes
    Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
        Route::get('/preferences', [ProfileController::class, 'getPreferences']);
        Route::post('/preferences', [ProfileController::class, 'updatePreference']);
        Route::get('/activity', [ProfileController::class, 'getActivityHistory']);
        Route::post('/verify/document', [ProfileController::class, 'uploadVerificationDocument']);
    });
    
    // Sara Voice Assistant Routes
    Route::middleware('auth:sanctum')->prefix('sara-voice')->group(function () {
        Route::post('/process', [SaraVoiceController::class, 'processVoiceInput']);
        Route::post('/text-to-speech', [SaraVoiceController::class, 'textToSpeech']);
        Route::get('/voices', [SaraVoiceController::class, 'getAvailableVoices']);
        
        // Voice Streaming Endpoints
        Route::post('/stream', [SaraVoiceController::class, 'streamAudio']);
        Route::post('/process-stream', [SaraVoiceController::class, 'processVoiceStream']);
        Route::get('/stream/{stream_id}', [SaraVoiceController::class, 'getStreamAudio']);
        Route::get('/voices-detailed', [SaraVoiceController::class, 'getVoices']);
    });
    
    // Voice Processing Routes (alternative endpoint)
    Route::middleware('auth:sanctum')->prefix('voice')->group(function () {
        Route::post('/process', [SaraVoiceController::class, 'processVoiceInput']);
    });
    
    // Admin Configuration Routes
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin/configuration')->group(function () {
        Route::get('/categories', [ConfigurationController::class, 'getCategories']);
        Route::get('/category/{category}', [ConfigurationController::class, 'getConfigurationByCategory']);
        Route::post('/update', [ConfigurationController::class, 'updateConfiguration']);
        Route::get('/env', [ConfigurationController::class, 'getExposedEnvironmentVariables']);
        Route::post('/env/update', [ConfigurationController::class, 'updateEnvironmentVariables']);
    });
    
    // Mobile App Specific Routes
    Route::prefix('mobile')->group(function () {
        Route::get('/config', [MobileAppController::class, 'getAppConfig']);
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/device', [MobileAppController::class, 'updateDeviceInfo']);
            Route::post('/register-device', [MobileAppController::class, 'updateDeviceInfo']); // Alias for device registration
            Route::post('/notification/test', [MobileAppController::class, 'testPushNotification']);
            Route::get('/notifications/preferences', [MobileAppController::class, 'getNotificationPreferences']);
            Route::post('/notifications/preferences', [MobileAppController::class, 'updateNotificationPreferences']);
        });
    });
    
    // App Configuration
    Route::get('/config', function () {
        return response()->json([
            'version' => config('app.version', '1.0.0'),
            'min_version' => '1.0.0',
            'force_update' => false,
            'maintenance' => app()->isDownForMaintenance(),
            'features' => [
                'sara_chatbot' => true,
                'instant_booking' => true,
                'social_login' => true,
                'push_notifications' => true
            ],
            'urls' => [
                'terms' => url('/terms'),
                'privacy' => url('/privacy'),
                'support' => url('/support')
            ]
        ]);
    });
});

// Health Check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String()
    ]);
});
