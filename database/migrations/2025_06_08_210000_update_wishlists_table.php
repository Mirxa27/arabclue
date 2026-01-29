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
        if (!Schema::hasColumn('wishlists', 'collection_id')) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->foreignId('collection_id')->nullable()->after('property_id');
                $table->text('note')->nullable()->after('collection_id');
                $table->json('tags')->nullable()->after('note');
                $table->boolean('is_private')->default(false)->after('tags');
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropForeign(['collection_id']);
            $table->dropColumn(['collection_id', 'note', 'tags', 'is_private']);
            $table->dropSoftDeletes();
        });
    }
};
