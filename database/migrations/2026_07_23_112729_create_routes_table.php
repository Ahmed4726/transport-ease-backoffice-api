<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->foreignId('start_city_id')
                ->constrained('cities')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('end_city_id')
                ->constrained('cities')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->decimal('distance',8,2)->nullable();

            $table->integer('estimated_duration')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
