<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_trip_stops', function (Blueprint $table) {
            $table->dropForeign(['route_stop_id']);
        });

        Schema::table('driver_trip_stops', function (Blueprint $table) {
            $table->foreignId('route_stop_id')->change();
        });

        Schema::table('driver_trip_stops', function (Blueprint $table) {
            $table->foreign('route_stop_id')->references('id')->on('city_stops')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('driver_trip_stops', function (Blueprint $table) {
            $table->dropForeign(['route_stop_id']);
        });

        Schema::table('driver_trip_stops', function (Blueprint $table) {
            $table->foreignId('route_stop_id')->change();
        });

        Schema::table('driver_trip_stops', function (Blueprint $table) {
            $table->foreign('route_stop_id')->references('id')->on('route_stops')->cascadeOnDelete();
        });
    }
};
