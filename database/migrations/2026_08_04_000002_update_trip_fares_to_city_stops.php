<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_fares', function (Blueprint $table) {
            $table->dropForeign(['from_stop_id']);
            $table->dropForeign(['to_stop_id']);
        });

        Schema::table('trip_fares', function (Blueprint $table) {
            $table->foreignId('from_stop_id')->change();
            $table->foreignId('to_stop_id')->change();
        });

        Schema::table('trip_fares', function (Blueprint $table) {
            $table->foreign('from_stop_id')->references('id')->on('city_stops')->cascadeOnDelete();
            $table->foreign('to_stop_id')->references('id')->on('city_stops')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trip_fares', function (Blueprint $table) {
            $table->dropForeign(['from_stop_id']);
            $table->dropForeign(['to_stop_id']);
        });

        Schema::table('trip_fares', function (Blueprint $table) {
            $table->foreignId('from_stop_id')->change();
            $table->foreignId('to_stop_id')->change();
        });

        Schema::table('trip_fares', function (Blueprint $table) {
            $table->foreign('from_stop_id')->references('id')->on('route_stops')->cascadeOnDelete();
            $table->foreign('to_stop_id')->references('id')->on('route_stops')->cascadeOnDelete();
        });
    }
};
