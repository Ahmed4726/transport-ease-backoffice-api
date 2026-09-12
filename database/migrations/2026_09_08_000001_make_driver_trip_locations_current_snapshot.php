<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tripIds = DB::table('driver_trip_locations')
            ->select('driver_trip_id')
            ->groupBy('driver_trip_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('driver_trip_id');

        foreach ($tripIds as $tripId) {
            $latest = DB::table('driver_trip_locations')
                ->where('driver_trip_id', $tripId)
                ->orderByDesc('recorded_at')
                ->orderByDesc('id')
                ->first(['id']);

            if ($latest) {
                DB::table('driver_trip_locations')
                    ->where('driver_trip_id', $tripId)
                    ->where('id', '<>', $latest->id)
                    ->delete();
            }
        }

        Schema::table('driver_trip_locations', function (Blueprint $table) {
            $table->unique('driver_trip_id', 'driver_trip_locations_trip_unique');
        });
    }

    public function down(): void
    {
        Schema::table('driver_trip_locations', function (Blueprint $table) {
            $table->dropUnique('driver_trip_locations_trip_unique');
        });
    }
};
