<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_stops', function (Blueprint $table) {

            $table->id();

            $table->foreignId('route_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('city_id')
                ->constrained('cities')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unsignedInteger('stop_order');

            $table->decimal('distance_from_start',8,2)
                ->nullable();

            $table->integer('estimated_minutes')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'route_id',
                'stop_order'
            ]);

            $table->unique([
                'route_id',
                'city_id'
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_stops');
    }
};
