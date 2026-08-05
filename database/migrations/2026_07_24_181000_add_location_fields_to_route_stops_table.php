<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('route_stops', function (Blueprint $table) {
            $table->dropUnique(['route_id', 'city_id']);

            $table->string('location_name')->nullable()->after('city_id');
            $table->string('address')->nullable()->after('location_name');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('google_place_id')->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('route_stops', function (Blueprint $table) {
            $table->dropColumn(['location_name', 'address', 'latitude', 'longitude', 'google_place_id']);
            $table->unique(['route_id', 'city_id']);
        });
    }
};
