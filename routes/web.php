<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SaraConfigController;
use App\Http\Controllers\EmailPreviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaraChatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\MessagingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


// Admin routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Sara AI Configuration Routes
    Route::get('/sara/config', [SaraConfigController::class, 'index'])->name('admin.sara.config');
    Route::post('/sara/save-config', [SaraConfigController::class, 'saveConfig'])->name('admin.sara.save-config');
    Route::post('/sara/reset-config', [SaraConfigController::class, 'resetConfig'])->name('admin.sara.reset-config');
    Route::get('/sara/export-config', [SaraConfigController::class, 'exportConfig'])->name('admin.sara.export-config');
    Route::post('/sara/import-config', [SaraConfigController::class, 'importConfig'])->name('admin.sara.import-config');
    Route::post('/sara/test', [SaraConfigController::class, 'testSara'])->name('admin.sara.test');
});

// Authentication routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
});

require __DIR__.'/auth.php';


/*
|--------------------------------------------------------------------------
| Web Routes - Main Application Routes
|--------------------------------------------------------------------------
*/

// Include Sara AI routes
require __DIR__.'/admin_sara_routes.php';


// Main Application Routes
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/stays', [PropertyController::class, 'index'])->name('stays.index');

Route::get('/sara', [SaraChatController::class, 'index'])->name('sara.chat');

// Additional main pages
Route::get('/host', [HomeController::class, 'hostLanding'])->name('host.landing');
Route::get('/invest', [HomeController::class, 'investLanding'])->name('invest.landing');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/stories', [HomeController::class, 'stories'])->name('stories');
Route::get('/blog', [HomeController::class, 'blog'])->name('blog');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/support', [HomeController::class, 'support'])->name('support');



Route::get('/properties/{property:slug}', [PropertyController::class, 'show'])->name('properties.show');

Route::middleware('auth')->group(function () {
    // Wishlist Routes
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/', [WishlistController::class, 'store'])->name('store');
        Route::put('/{wishlist}', [WishlistController::class, 'update'])->name('update');
        Route::delete('/{wishlist}', [WishlistController::class, 'destroy'])->name('destroy');
        Route::post('/toggle', [WishlistController::class, 'toggle'])->name('toggle');
        Route::post('/bulk-action', [WishlistController::class, 'bulkAction'])->name('bulk-action');
        Route::post('/move-to-collection', [WishlistController::class, 'moveToCollection'])->name('move-to-collection');
        Route::delete('/clear', [WishlistController::class, 'clear'])->name('clear');
        
        // Sharing
        Route::get('/share-form', [WishlistController::class, 'shareForm'])->name('share-form');
        Route::post('/share', [WishlistController::class, 'share'])->name('share');
        Route::get('/shared/{token}', [WishlistController::class, 'shared'])->name('shared');
        
        // Collections
        Route::prefix('collections')->name('collections.')->group(function () {
            Route::post('/', [WishlistController::class, 'createCollection'])->name('create');
            Route::get('/{collection}', [WishlistController::class, 'showCollection'])->name('show');
            Route::put('/{collection}', [WishlistController::class, 'updateCollection'])->name('update');
            Route::delete('/{collection}', [WishlistController::class, 'deleteCollection'])->name('delete');
            Route::post('/{collection}/share', [WishlistController::class, 'shareCollection'])->name('share');
            Route::get('/shared/{token}', [WishlistController::class, 'sharedCollection'])->name('shared');
        });
    });
    
    // Legacy route for backward compatibility
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::get('/messages', [MessagingController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [MessagingController::class, 'show'])->name('messages.conversation');
    Route::post('/messages/{conversation}', [MessagingController::class, 'storeMessage'])->name('messages.store');
    Route::post('/messages', [MessagingController::class, 'createConversation'])->name('messages.create');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    
    // Dispute Management Routes
    Route::prefix('disputes')->name('disputes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DisputeController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\DisputeController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\DisputeController::class, 'store'])->name('store');
        Route::get('/{dispute}', [\App\Http\Controllers\DisputeController::class, 'show'])->name('show');
        Route::post('/{dispute}/message', [\App\Http\Controllers\DisputeController::class, 'addMessage'])->name('message');
        Route::get('/{dispute}/attachment/{messageId}', [\App\Http\Controllers\DisputeController::class, 'downloadAttachment'])->name('attachment');
    });
    
    // Referral System Routes
    Route::prefix('referrals')->name('referrals.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReferralController::class, 'index'])->name('index');
        Route::post('/send-invites', [\App\Http\Controllers\ReferralController::class, 'sendInvites'])->name('send-invites');
        Route::post('/apply-credits', [\App\Http\Controllers\ReferralController::class, 'applyCredits'])->name('apply-credits');
        Route::get('/credits', [\App\Http\Controllers\ReferralController::class, 'credits'])->name('credits');
        Route::get('/program', [\App\Http\Controllers\ReferralController::class, 'program'])->name('program');
    });
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
// Admin login routes simply reuse the standard login controller
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Simple user dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/search', [SearchController::class, 'index'])->name('search');

// Admin Routes (Web Interface)
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard.index');

    // User Management
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.users.show');

    // Property Management
    Route::get('/properties', [\App\Http\Controllers\Admin\PropertyController::class, 'index'])->name('admin.properties.index');
    Route::get('/properties/{property}', [\App\Http\Controllers\Admin\PropertyController::class, 'show'])->name('admin.properties.show');

    // Booking Management
    Route::get('/bookings', [\App\Http\Controllers\Admin\BookingController::class, 'index'])->name('admin.bookings.index');
    Route::get('/bookings/{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'show'])->name('admin.bookings.show');

    // Sara AI Configuration
    Route::prefix('sara')->name('sara.')->group(function () {
        Route::get('/config', [\App\Http\Controllers\Admin\SaraConfigController::class, 'index'])->name('config');
        Route::post('/config/save', [\App\Http\Controllers\Admin\SaraConfigController::class, 'saveConfig'])->name('save-config');
        Route::post('/config/reset', [\App\Http\Controllers\Admin\SaraConfigController::class, 'resetConfig'])->name('reset-config');
        Route::get('/config/export', [\App\Http\Controllers\Admin\SaraConfigController::class, 'exportConfig'])->name('export-config');
        Route::post('/config/import', [\App\Http\Controllers\Admin\SaraConfigController::class, 'importConfig'])->name('import-config');
        Route::post('/config/test-api', [\App\Http\Controllers\Admin\SaraConfigController::class, 'testApiConnection'])->name('test-api');
        Route::post('/config/test-message', [\App\Http\Controllers\Admin\SaraConfigController::class, 'testMessage'])->name('test-message');
        Route::get('/config/stats', [\App\Http\Controllers\Admin\SaraConfigController::class, 'getUsageStats'])->name('stats');
        Route::get('/conversations', [\App\Http\Controllers\Admin\SaraConfigController::class, 'viewConversations'])->name('view-conversations');
        Route::get('/conversations/{id}', [\App\Http\Controllers\Admin\SaraConfigController::class, 'viewConversation'])->name('view-conversation');
        Route::get('/export-stats', [\App\Http\Controllers\Admin\SaraConfigController::class, 'exportStats'])->name('export-stats');
    });

    // Content Management
    Route::get('/content', [\App\Http\Controllers\Admin\ContentController::class, 'index'])->name('admin.content.index');
    Route::get('/content/pages', [\App\Http\Controllers\Admin\ContentController::class, 'pages'])->name('admin.content.pages');
    Route::get('/content/sliders', [\App\Http\Controllers\Admin\ContentController::class, 'sliders'])->name('admin.content.sliders');

    // Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('admin.settings.index');
    Route::get('/settings/payment-gateways', [\App\Http\Controllers\Admin\SettingsController::class, 'paymentGateways'])->name('admin.settings.payment-gateways');
    Route::get('/settings/currencies', [\App\Http\Controllers\Admin\SettingsController::class, 'currencies'])->name('admin.settings.currencies');
    Route::get('/settings/languages', [\App\Http\Controllers\Admin\SettingsController::class, 'languages'])->name('admin.settings.languages');

    // Reports
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/revenue', [\App\Http\Controllers\Admin\ReportController::class, 'revenue'])->name('admin.reports.revenue');
    Route::get('/reports/bookings', [\App\Http\Controllers\Admin\ReportController::class, 'bookings'])->name('admin.reports.bookings');

    // Coupon Management
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CouponController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\CouponController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\CouponController::class, 'store'])->name('store');
        Route::get('/{coupon}', [\App\Http\Controllers\Admin\CouponController::class, 'show'])->name('show');
        Route::get('/{coupon}/edit', [\App\Http\Controllers\Admin\CouponController::class, 'edit'])->name('edit');
        Route::put('/{coupon}', [\App\Http\Controllers\Admin\CouponController::class, 'update'])->name('update');
        Route::delete('/{coupon}', [\App\Http\Controllers\Admin\CouponController::class, 'destroy'])->name('destroy');
        Route::post('/{coupon}/toggle-status', [\App\Http\Controllers\Admin\CouponController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/generate-bulk', [\App\Http\Controllers\Admin\CouponController::class, 'generateBulk'])->name('generate-bulk');
        Route::get('/export/csv', [\App\Http\Controllers\Admin\CouponController::class, 'export'])->name('export');
        Route::post('/validate', [\App\Http\Controllers\Admin\CouponController::class, 'validateCoupon'])->name('validate');
        Route::get('/analytics/data', [\App\Http\Controllers\Admin\CouponController::class, 'analytics'])->name('analytics');
    });

    // .env Editor (Admin)
    Route::get('/env', [\App\Http\Controllers\Admin\SaraConfigController::class, 'showEnvEditor'])->name('admin.env.editor');
    Route::post('/env', [\App\Http\Controllers\Admin\SaraConfigController::class, 'updateEnv'])->name('admin.env.update');
});

// Host Routes (Web Interface)
Route::prefix('host')->middleware(['auth', 'host'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Host\DashboardController::class, 'index'])->name('host.dashboard');
    Route::get('/dashboard', [\App\Http\Controllers\Host\DashboardController::class, 'index'])->name('host.dashboard.index');
    Route::get('/properties', [\App\Http\Controllers\Host\PropertyController::class, 'index'])->name('host.properties.index');
    Route::get('/properties/create', [\App\Http\Controllers\Host\PropertyController::class, 'create'])->name('host.properties.create');
    Route::get('/properties/{property}', [\App\Http\Controllers\Host\PropertyController::class, 'show'])->name('host.properties.show');
    Route::get('/properties/{property}/edit', [\App\Http\Controllers\Host\PropertyController::class, 'edit'])->name('host.properties.edit');
    Route::get('/bookings', [\App\Http\Controllers\Host\BookingController::class, 'index'])->name('host.bookings.index');
    Route::get('/calendar', [\App\Http\Controllers\Host\CalendarController::class, 'index'])->name('host.calendar.index');
    Route::get('/earnings', [\App\Http\Controllers\Host\EarningController::class, 'index'])->name('host.earnings');
    Route::get('/financial-reports', [\App\Http\Controllers\Host\FinancialReportController::class, 'index'])->name('host.reports.financial');
    Route::get('/channel-manager', [\App\Http\Controllers\Host\ChannelManagerController::class, 'index'])->name('host.channel-manager.index');
});

// Additional routes can be added here as needed

// Email Preview Routes (for testing and development)
Route::prefix('email-preview')->name('email-preview.')->group(function () {
    Route::get('/', [EmailPreviewController::class, 'index'])->name('index');
    Route::get('/welcome', [EmailPreviewController::class, 'welcome'])->name('welcome');
    Route::get('/property-approved', [EmailPreviewController::class, 'propertyApproved'])->name('property-approved');
    Route::get('/property-rejected', [EmailPreviewController::class, 'propertyRejected'])->name('property-rejected');
    Route::get('/payment-confirmation', [EmailPreviewController::class, 'paymentConfirmation'])->name('payment-confirmation');
    Route::get('/review-request', [EmailPreviewController::class, 'reviewRequest'])->name('review-request');
    Route::get('/booking-reminder-checkin', [EmailPreviewController::class, 'bookingReminderCheckin'])->name('booking-reminder-checkin');
    Route::get('/booking-reminder-checkout', [EmailPreviewController::class, 'bookingReminderCheckout'])->name('booking-reminder-checkout');
    Route::get('/host-payout', [EmailPreviewController::class, 'hostPayout'])->name('host-payout');
    Route::get('/system-maintenance', [EmailPreviewController::class, 'systemMaintenance'])->name('system-maintenance');
    Route::get('/special-offer', [EmailPreviewController::class, 'specialOffer'])->name('special-offer');
});

// End of routes
