<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Theme Management System Migration
 * Implements flexible schema for runtime UI customization
 * utilizing JSON columns for component configuration
 */
return new class extends Migration
{
    /**
     * Execute migration - Create theme management infrastructure
     */
    public function up(): void
    {
        // Theme configurations table
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique()->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('color_scheme')->comment('Primary, secondary, accent colors');
            $table->json('typography')->comment('Font families, sizes, weights');
            $table->json('spacing')->comment('Padding, margins, gaps');
            $table->json('components')->comment('Component-specific styling');
            $table->json('animations')->comment('Transition and animation settings');
            $table->json('breakpoints')->comment('Responsive breakpoint overrides');
            $table->string('preview_image')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            // Index for active theme (constraint enforced in model)
            if (!Schema::hasIndex('themes', 'themes_is_active_index')) {
                $table->index(['is_active']);
            }
        });

        // Dynamic page layouts
        Schema::create('page_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('page_identifier')->unique()->index();
            $table->string('title');
            $table->json('sections')->comment('Ordered array of section configurations');
            $table->json('meta_data')->nullable();
            $table->json('schema_markup')->nullable()->comment('SEO structured data');
            $table->boolean('is_published')->default(true);
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->index(['theme_id', 'page_identifier']);
        });

        // Reusable UI components
        Schema::create('ui_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('component_type')->index()->comment('hero, feature, testimonial, etc');
            $table->json('props')->comment('Component property values');
            $table->json('responsive_props')->nullable()->comment('Breakpoint-specific props');
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();
            $table->boolean('is_global')->default(false);
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->index(['theme_id', 'component_type']);
        });

        // Dynamic content blocks for AI generation
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique()->index();
            $table->string('block_type')->index()->comment('text, image, video, carousel');
            $table->json('content')->comment('Multilingual content storage');
            $table->json('ai_metadata')->nullable()->comment('AI generation parameters');
            $table->string('ai_model_used')->nullable();
            $table->float('ai_confidence_score')->nullable();
            $table->boolean('ai_generated')->default(false);
            $table->boolean('human_reviewed')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            $table->index(['block_type', 'ai_generated']);
        });

        // Theme customization history
        Schema::create('theme_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->json('changes')->comment('Diff of changes');
            $table->string('change_type')->comment('color, typography, layout, etc');
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamp('created_at');
            
            $table->index(['theme_id', 'created_at']);
        });

        // AI content generation queue
        Schema::create('ai_content_queue', function (Blueprint $table) {
            $table->id();
            $table->string('content_type')->index();
            $table->json('parameters')->comment('Generation parameters');
            $table->string('target_identifier')->comment('Where content will be placed');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending')->index();
            $table->json('result')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->foreignId('requested_by')->constrained('users');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
        });

        // Mobile app configuration
        Schema::create('mobile_app_config', function (Blueprint $table) {
            $table->id();
            $table->string('config_key')->unique();
            $table->json('config_value');
            $table->string('platform')->comment('ios, android, pwa');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['platform', 'is_active']);
        });

        // Admin UI preferences
        Schema::create('admin_ui_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('dashboard_layout')->nullable();
            $table->json('widget_preferences')->nullable();
            $table->json('theme_editor_settings')->nullable();
            $table->string('preferred_language')->default('en');
            $table->boolean('dark_mode')->default(false);
            $table->boolean('compact_view')->default(false);
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse migration
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_ui_preferences');
        Schema::dropIfExists('mobile_app_config');
        Schema::dropIfExists('ai_content_queue');
        Schema::dropIfExists('theme_revisions');
        Schema::dropIfExists('content_blocks');
        Schema::dropIfExists('ui_components');
        Schema::dropIfExists('page_layouts');
        Schema::dropIfExists('themes');
    }
};
