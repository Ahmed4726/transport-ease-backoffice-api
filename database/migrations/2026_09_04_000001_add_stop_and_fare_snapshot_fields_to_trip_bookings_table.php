<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_bookings', function (Blueprint $table) {
            $table->foreignId('from_stop_id')->nullable()->after('passenger_id')->constrained('city_stops')->nullOnDelete();
            $table->foreignId('to_stop_id')->nullable()->after('from_stop_id')->constrained('city_stops')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trip_bookings', function (Blueprint $table) {
            $table->dropForeign(['from_stop_id']);
            $table->dropForeign(['to_stop_id']);
            $table->dropColumn(['from_stop_id', 'to_stop_id']);
        });
    }
};
