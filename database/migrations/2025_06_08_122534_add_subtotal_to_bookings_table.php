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
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('bookings', 'cleaning_fee')) {
                $table->decimal('cleaning_fee', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('bookings', 'service_fee')) {
                $table->decimal('service_fee', 10, 2)->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('subtotal');
            $table->dropColumn('cleaning_fee');
            $table->dropColumn('service_fee');
        });
    }
};
