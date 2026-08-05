<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('cnic')->unique();

            $table->string('license_number')->unique();

            $table->date('license_expiry');

            $table->string('profile_photo');

            $table->string('cnic_front');

            $table->string('cnic_back');

            $table->string('license_front');

            $table->string('license_back');

            $table->string('address')->nullable();

            $table->string('city')->nullable();

            $table->date('date_of_birth')->nullable();

            $table->string('emergency_contact_name')->nullable();

            $table->string('emergency_contact_phone')->nullable();

            $table->string('blood_group')->nullable();

            $table->boolean('is_available')->default(false);

            $table->decimal('rating', 3, 2)->default(5.00);

            $table->unsignedInteger('completed_trips')->default(0);

            $table->text('remarks')->nullable();

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

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
