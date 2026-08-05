<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_trip_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_trip_id')->constrained('driver_trips')->cascadeOnDelete();
            $table->foreignId('route_stop_id')->constrained('route_stops')->cascadeOnDelete();
            $table->unsignedInteger('stop_order')->default(0);
            $table->timestamps();

            $table->unique(['driver_trip_id', 'route_stop_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_trip_stops');
    }
};
