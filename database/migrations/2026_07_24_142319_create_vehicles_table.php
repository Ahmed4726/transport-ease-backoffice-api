<?php

use App\Enums\VehicleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {

            $table->id();

            $table->foreignId('driver_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('vehicle_type_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('brand');

            $table->string('model');

            $table->year('manufacture_year');

            $table->string('color');

            $table->string('registration_number')->unique();

            $table->string('engine_number')->unique();

            $table->string('chassis_number')->unique();

            $table->unsignedTinyInteger('total_seats');

            $table->unsignedTinyInteger('available_seats');

            $table->string('vehicle_photo');

            $table->string('registration_book');

            $table->string('fitness_certificate')->nullable();

            $table->string('insurance_document')->nullable();

            $table->string('status')->default(VehicleStatus::PENDING->value);

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('rejected_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
