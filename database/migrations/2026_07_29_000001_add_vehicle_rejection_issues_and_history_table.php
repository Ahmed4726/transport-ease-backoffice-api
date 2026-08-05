<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'rejection_issues')) {
                $table->json('rejection_issues')->nullable()->after('remarks');
            }
        });

        Schema::create('vehicle_rejection_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('remarks');
            $table->json('rejection_issues')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_rejection_histories');

        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'rejection_issues')) {
                $table->dropColumn('rejection_issues');
            }
        });
    }
};
