<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_trips', function (Blueprint $table) {

            $table->id();

            $table->foreignId('driver_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('vehicle_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('route_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->date('trip_date');

            $table->time('departure_time');

            $table->unsignedInteger('total_seats');

            $table->unsignedInteger('available_seats');

            $table->boolean('is_instant')->default(false);

            $table->enum('status',[
                'scheduled',
                'started',
                'completed',
                'cancelled'
            ])->default('scheduled');

            $table->timestamp('started_at')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index([
                'trip_date',
                'status'
            ]);

            $table->index([
                'route_id',
                'trip_date'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_trips');
    }
};
