<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wishlist_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->boolean('is_private')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Add collection_id to existing wishlists table
        Schema::table('wishlists', function (Blueprint $table) {
            if (!Schema::hasColumn('wishlists', 'collection_id')) {
                $table->foreignId('collection_id')->nullable()->after('property_id')
                    ->constrained('wishlist_collections')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('wishlists', 'tags')) {
                $table->json('tags')->nullable()->after('note');
            }
            
            if (!Schema::hasColumn('wishlists', 'note')) {
                $table->text('note')->nullable()->after('property_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            if (Schema::hasColumn('wishlists', 'collection_id')) {
                $table->dropForeign(['collection_id']);
                $table->dropColumn('collection_id');
            }
            
            if (Schema::hasColumn('wishlists', 'tags')) {
                $table->dropColumn('tags');
            }
            
            if (Schema::hasColumn('wishlists', 'note')) {
                $table->dropColumn('note');
            }
        });

        Schema::dropIfExists('wishlist_collections');
    }
};
