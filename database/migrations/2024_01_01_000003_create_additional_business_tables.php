<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additional Business Tables Migration
 * Implements extended features for wishlists, messaging, coupons,
 * disputes, and administrative management
 */
return new class extends Migration
{
    public function up(): void
    {
        // User wishlists
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('note')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_private')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['user_id', 'property_id']);
            $table->index(['user_id', 'is_private']);
        });

        // User conversations for messaging
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('guest_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject')->nullable();
            $table->enum('status', ['active', 'archived', 'resolved'])->default('active');
            $table->boolean('guest_read')->default(true);
            $table->boolean('host_read')->default(false);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            
            $table->index(['guest_id', 'status']);
            $table->index(['host_id', 'status']);
        });

        // Messages within conversations
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_system_message')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['conversation_id', 'created_at']);
            $table->index(['receiver_id', 'read_at']);
        });

        // Coupon codes and discounts
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount', 'free_nights']);
            $table->decimal('value', 10, 2);
            $table->decimal('minimum_amount', 10, 2)->nullable();
            $table->decimal('maximum_discount', 10, 2)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_per_user')->default(1);
            $table->integer('used_count')->default(0);
            $table->date('starts_at');
            $table->date('expires_at');
            $table->json('applicable_properties')->nullable();
            $table->json('user_restrictions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['code', 'is_active']);
            $table->index(['starts_at', 'expires_at']);
        });

        // Coupon usage tracking
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();
            
            $table->index(['coupon_id', 'user_id']);
        });

        // Dispute resolution system
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->string('dispute_id', 20)->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('users');
            $table->foreignId('host_id')->constrained('users');
            $table->foreignId('property_id')->constrained();
            $table->enum('type', ['refund', 'quality', 'cancellation', 'damage', 'other']);
            $table->string('title');
            $table->text('description');
            $table->json('evidence')->nullable();
            $table->decimal('claimed_amount', 10, 2)->nullable();
            $table->enum('status', ['open', 'investigating', 'awaiting_response', 'resolved', 'closed'])->default('open');
            $table->enum('resolution', ['guest_favor', 'host_favor', 'partial_refund', 'no_action'])->nullable();
            $table->decimal('resolution_amount', 10, 2)->nullable();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index(['guest_id', 'status']);
            $table->index(['host_id', 'status']);
        });

        // Dispute messages
        Schema::create('dispute_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users');
            $table->enum('sender_role', ['guest', 'host', 'admin']);
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
            
            $table->index(['dispute_id', 'created_at']);
        });

        // Property calendar blocking
        Schema::create('property_calendar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['available', 'booked', 'blocked', 'maintenance'])->default('available');
            $table->decimal('price', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            
            $table->unique(['property_id', 'date']);
            $table->index(['property_id', 'status']);
        });

        // Property price rules for dynamic pricing
        Schema::create('property_price_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['seasonal', 'weekly', 'event', 'last_minute', 'early_bird']);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('advance_days')->nullable();
            $table->json('days_of_week')->nullable();
            $table->decimal('adjustment_percentage', 5, 2)->nullable();
            $table->decimal('fixed_price', 10, 2)->nullable();
            $table->integer('minimum_nights')->nullable();
            $table->integer('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['property_id', 'is_active']);
        });

        // Admin UI preferences
        if (!Schema::hasTable('admin_ui_preferences')) {
            Schema::create('admin_ui_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('theme', 20)->default('light');
                $table->string('sidebar_collapsed')->default('false');
                $table->json('dashboard_widgets')->nullable();
                $table->json('table_preferences')->nullable();
                $table->string('language', 10)->default('en');
                $table->string('timezone', 50)->default('Asia/Riyadh');
                $table->json('notification_preferences')->nullable();
                $table->timestamps();
                
                $table->unique('user_id');
            });
        }

        // Referral tracking
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->string('referral_code', 20)->unique();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('referred_email')->nullable();
            $table->enum('status', ['pending', 'registered', 'completed'])->default('pending');
            $table->decimal('reward_amount', 10, 2)->default(0);
            $table->boolean('reward_paid')->default(false);
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['referrer_id', 'status']);
            $table->index(['referral_code']);
        });

        // Property external listings (channel manager)
        Schema::create('property_external_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->enum('channel', ['airbnb', 'booking.com', 'expedia', 'agoda', 'vrbo']);
            $table->string('external_id');
            $table->string('listing_url')->nullable();
            $table->enum('sync_status', ['active', 'paused', 'error', 'pending']);
            $table->timestamp('last_sync_at')->nullable();
            $table->json('sync_settings')->nullable();
            $table->json('last_error')->nullable();
            $table->timestamps();
            
            $table->unique(['property_id', 'channel']);
            $table->index(['channel', 'sync_status']);
        });

        // Email templates for automated communications
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('subject');
            $table->text('body_html');
            $table->text('body_text')->nullable();
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Activity logs for audit trail
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->string('batch_uuid')->nullable();
            $table->timestamps();
            
            $table->index('log_name');
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
        });

        // Analytics and metrics tracking
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['event_name', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // Conversation metrics for Sara AI
        Schema::create('conversation_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('sara_conversations')->cascadeOnDelete();
            $table->integer('duration_minutes');
            $table->integer('message_count');
            $table->integer('user_messages');
            $table->integer('assistant_messages');
            $table->string('channel');
            $table->boolean('has_booking')->default(false);
            $table->string('intent')->nullable();
            $table->decimal('satisfaction_score', 3, 2)->nullable();
            $table->json('feedback')->nullable();
            $table->timestamps();
            
            $table->index(['conversation_id']);
            $table->index(['channel', 'has_booking']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_metrics');
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('property_external_listings');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('admin_ui_preferences');
        Schema::dropIfExists('property_price_rules');
        Schema::dropIfExists('property_calendar');
        Schema::dropIfExists('dispute_messages');
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('wishlists');
    }
};
