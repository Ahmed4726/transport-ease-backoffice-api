<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_trip_id')->constrained('driver_trips')->cascadeOnDelete();
            $table->foreignId('passenger_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('seats');
            $table->enum('status', ['confirmed', 'cancelled', 'boarded', 'completed', 'no_show'])->default('confirmed');
            $table->decimal('fare_per_seat', 10, 2)->nullable();
            $table->decimal('total_fare', 10, 2)->nullable();
            $table->string('booking_reference', 20)->unique();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 1000)->nullable();
            $table->timestamp('boarded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('no_show_at')->nullable();
            $table->timestamps();

            $table->index(['driver_trip_id', 'status']);
            $table->index(['passenger_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_bookings');
    }
};
