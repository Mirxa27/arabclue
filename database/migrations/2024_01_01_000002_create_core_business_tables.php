<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core Business Tables Migration
 * Implements normalized schema with advanced indexing
 * for high-performance property and booking management
 */
return new class extends Migration
{
    public function up(): void
    {
        // Enhanced users table with mobile app support
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 20)->nullable()->index();
            $table->enum('role', ['guest', 'host', 'admin'])->default('guest')->index();
            $table->string('avatar')->nullable();
            $table->string('language', 10)->default('en');
            $table->text('bio')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->index();
            
            // OAuth integration
            $table->string('google_id')->nullable()->unique();
            $table->string('facebook_id')->nullable()->unique();
            $table->string('apple_id')->nullable()->unique();
            
            // Mobile app tokens
            $table->string('fcm_token')->nullable()->comment('Firebase Cloud Messaging');
            $table->string('apns_token')->nullable()->comment('Apple Push Notification');
            $table->json('device_info')->nullable();
            
            // Enhanced profile
            $table->json('preferences')->nullable()->comment('User preferences JSON');
            $table->json('notification_settings')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            
            // Verification and compliance
            $table->boolean('identity_verified')->default(false);
            $table->timestamp('identity_verified_at')->nullable();
            $table->string('government_id')->nullable();
            
            // Performance tracking
            $table->decimal('host_rating', 3, 2)->nullable();
            $table->decimal('guest_rating', 3, 2)->nullable();
            $table->integer('total_bookings')->default(0);
            $table->integer('total_listings')->default(0);
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            // Composite indexes for performance
            $table->index(['role', 'status']);
            $table->index(['email', 'status']);
        });

        // Properties table with enhanced features
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('property_type', ['house', 'apartment', 'villa', 'studio', 'room'])->index();
            $table->enum('room_type', ['entire_place', 'private_room', 'shared_room'])->index();
            
            // Capacity and specifications
            $table->integer('accommodates')->default(1);
            $table->integer('bedrooms')->default(1);
            $table->integer('beds')->default(1);
            $table->decimal('bathrooms', 3, 1)->default(1.0);
            $table->integer('square_meters')->nullable();
            
            // Pricing with dynamic rates
            $table->decimal('price_per_night', 10, 2);
            $table->decimal('cleaning_fee', 10, 2)->default(0);
            $table->decimal('service_fee_percentage', 5, 2)->default(10.00);
            $table->json('seasonal_pricing')->nullable();
            $table->json('length_of_stay_pricing')->nullable();
            $table->json('special_offers')->nullable();
            
            // Location with geocoding
            $table->string('address');
            $table->string('address_line_2')->nullable();
            $table->string('city')->index();
            $table->string('state')->nullable();
            $table->string('country')->default('Saudi Arabia');
            $table->string('postal_code', 20)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            // MariaDB requires spatially indexed columns to be NOT NULL.
            // Avoid adding the spatial index to maintain compatibility.
            $table->point('location')->nullable();
            $table->string('neighborhood')->nullable()->index();
            
            // House rules and policies
            $table->time('check_in_time')->default('15:00:00');
            $table->time('check_out_time')->default('11:00:00');
            $table->json('house_rules')->nullable();
            $table->enum('cancellation_policy', ['flexible', 'moderate', 'strict', 'super_strict'])->default('flexible');
            $table->boolean('instant_booking')->default(false)->index();
            $table->integer('minimum_nights')->default(1);
            $table->integer('maximum_nights')->default(365);
            
            // AI and smart features
            $table->json('ai_generated_description')->nullable();
            $table->json('ai_suggested_amenities')->nullable();
            $table->float('ai_pricing_suggestion')->nullable();
            $table->boolean('smart_pricing_enabled')->default(false);
            
            // Status and visibility
            $table->boolean('is_featured')->default(false)->index();
            $table->enum('status', ['draft', 'pending', 'active', 'inactive', 'suspended'])->default('draft')->index();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            
            // Performance metrics
            $table->integer('views')->default(0);
            $table->integer('saves')->default(0);
            $table->integer('shares')->default(0);
            $table->decimal('overall_rating', 3, 2)->nullable();
            $table->integer('review_count')->default(0);
            $table->decimal('occupancy_rate', 5, 2)->nullable();
            
            // SEO and marketing
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Composite indexes
            $table->index(['user_id', 'status']);
            $table->index(['city', 'status', 'is_featured']);
            $table->index(['property_type', 'room_type', 'status']);
        });

        // Advanced booking system
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference', 10)->unique();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('host_id')->constrained('users');
            
            // Booking details
            $table->date('check_in')->index();
            $table->date('check_out')->index();
            $table->integer('guests');
            $table->integer('adults');
            $table->integer('children')->default(0);
            $table->integer('infants')->default(0);
            $table->json('guest_details')->nullable();
            
            // Pricing breakdown
            $table->decimal('price_per_night', 10, 2);
            $table->integer('total_nights');
            $table->decimal('accommodation_total', 10, 2);
            $table->decimal('cleaning_fee', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('host_service_fee', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('coupon_code')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('SAR');
            $table->decimal('exchange_rate', 10, 6)->default(1.000000);
            
            // Status management
            $table->enum('status', [
                'inquiry', 'pending', 'accepted', 'declined', 
                'expired', 'cancelled_by_guest', 'cancelled_by_host', 
                'completed', 'reviewed'
            ])->default('pending')->index();
            
            // Payment information
            $table->enum('payment_status', [
                'pending', 'authorized', 'partially_paid',
                'paid', 'refunded', 'failed'
            ])->default('pending')->index();
            $table->string('payment_method')->nullable();
            $table->string('payment_intent_id')->nullable()->index();
            $table->json('payment_details')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Communication
            $table->text('special_requests')->nullable();
            $table->text('host_message')->nullable();
            $table->text('guest_message')->nullable();
            $table->boolean('guest_agreed_to_rules')->default(false);
            
            // Cancellation details
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->enum('refund_status', ['pending', 'processing', 'completed', 'failed'])->nullable();
            
            // Check-in/out tracking
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->json('check_in_details')->nullable();
            
            // AI assistant interaction
            $table->boolean('booked_via_sara')->default(false);
            $table->string('sara_conversation_id')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Composite indexes
            $table->index(['property_id', 'check_in', 'check_out']);
            $table->index(['user_id', 'status']);
            $table->index(['host_id', 'status']);
            $table->index(['check_in', 'status']);
        });

        // Property amenities with categories
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->enum('category', [
                'basic', 'safety', 'kitchen', 'bathroom', 
                'bedroom', 'entertainment', 'outdoor', 'parking',
                'accessibility', 'family', 'luxury'
            ])->index();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_popular')->default(false);
            $table->timestamps();
        });

        // Property amenities pivot
        Schema::create('property_amenities', function (Blueprint $table) {
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->primary(['property_id', 'amenity_id']);
        });

        // Property images with AI analysis
        Schema::create('property_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('caption')->nullable();
            $table->json('ai_tags')->nullable()->comment('AI-detected image tags');
            $table->json('ai_quality_score')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_verified')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
            
            $table->index(['property_id', 'sort_order']);
        });

        // Enhanced reviews with detailed ratings
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('host_id')->constrained('users');
            
            // Overall and category ratings
            $table->decimal('rating', 2, 1);
            $table->integer('cleanliness_rating')->nullable();
            $table->integer('communication_rating')->nullable();
            $table->integer('checkin_rating')->nullable();
            $table->integer('accuracy_rating')->nullable();
            $table->integer('location_rating')->nullable();
            $table->integer('value_rating')->nullable();
            
            // Review content
            $table->text('comment');
            $table->json('highlighted_amenities')->nullable();
            $table->json('improvement_suggestions')->nullable();
            
            // AI analysis
            $table->json('sentiment_analysis')->nullable();
            $table->float('sentiment_score')->nullable();
            $table->json('extracted_keywords')->nullable();
            
            // Host response
            $table->text('host_response')->nullable();
            $table->timestamp('host_responded_at')->nullable();
            
            // Moderation
            $table->boolean('is_verified')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->text('moderation_notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['property_id', 'rating']);
            $table->index(['user_id', 'created_at']);
        });

        // Sara AI chatbot conversations
        Schema::create('sara_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('channel')->default('web')->comment('web, mobile, whatsapp');
            $table->json('context')->nullable();
            $table->json('user_preferences')->nullable();
            $table->string('intent')->nullable()->index();
            $table->enum('status', ['active', 'completed', 'abandoned'])->default('active');
            $table->timestamp('last_activity_at');
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });

        // Sara AI messages
        Schema::create('sara_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('sara_conversations')->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->text('content');
            $table->json('metadata')->nullable();
            $table->json('suggested_actions')->nullable();
            $table->string('intent')->nullable();
            $table->float('confidence_score')->nullable();
            $table->integer('tokens_used')->nullable();
            $table->timestamps();
            
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sara_messages');
        Schema::dropIfExists('sara_conversations');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('property_images');
        Schema::dropIfExists('property_amenities');
        Schema::dropIfExists('amenities');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('users');
    }
};
