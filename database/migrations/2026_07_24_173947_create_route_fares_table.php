<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_fares', function (Blueprint $table) {

            $table->id();

            $table->foreignId('route_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('from_stop_id')
                ->constrained('route_stops')
                ->cascadeOnDelete();

            $table->foreignId('to_stop_id')
                ->constrained('route_stops')
                ->cascadeOnDelete();

            $table->decimal('fare',10,2);

            $table->timestamps();

            $table->unique([
                'route_id',
                'from_stop_id',
                'to_stop_id'
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_fares');
    }
};
