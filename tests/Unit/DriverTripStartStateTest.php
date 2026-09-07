<?php

namespace Tests\Unit;

use App\Http\Controllers\API\DriverTripController;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Driver;
use App\Models\DriverTrip;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Support\Facades\Date;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverTripStartStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_starting_trip_marks_state_and_timestamp(): void
    {
        $controller = new class extends DriverTripController {
        };

        $driver = $this->driver();
        $trip = new DriverTrip([
            'driver_id' => $driver->id,
            'vehicle_id' => $driver->vehicles()->first()->id,
            'trip_date' => now()->toDateString(),
            'departure_time' => '10:00',
            'total_seats' => 4,
            'available_seats' => 4,
            'status' => 'scheduled',
            'started_at' => null,
        ]);

        $reflection = new \ReflectionMethod($controller, 'applyTripStartState');
        $reflection->setAccessible(true);
        $result = $reflection->invoke($controller, $trip);

        $this->assertSame('started', $result->status);
        $this->assertNotNull($result->started_at);
    }

    public function test_starting_trip_accepts_current_coordinates(): void
    {
        $controller = new class extends DriverTripController {
        };

        $driver = $this->driver();
        $trip = new DriverTrip([
            'driver_id' => $driver->id,
            'vehicle_id' => $driver->vehicles()->first()->id,
            'trip_date' => now()->toDateString(),
            'departure_time' => '10:00',
            'total_seats' => 4,
            'available_seats' => 4,
            'status' => 'scheduled',
            'started_at' => null,
        ]);

        $reflection = new \ReflectionMethod($controller, 'applyTripStartState');
        $reflection->setAccessible(true);

        $result = $reflection->invokeArgs($controller, [$trip, 24.8607, 67.0011]);

        $this->assertSame('started', $result->status);
        $this->assertNotNull($result->started_at);
    }

    private function driver(): Driver
    {
        $user = User::create([
            'name' => 'Test Driver',
            'email' => uniqid('driver-', true).'@test.local',
            'phone' => uniqid('03', true),
            'password' => 'password',
            'role' => UserRole::DRIVER,
            'status' => UserStatus::APPROVED,
        ]);

        $driver = Driver::create([
            'user_id' => $user->id,
            'cnic' => uniqid('cnic-', true),
            'license_number' => uniqid('license-', true),
            'license_expiry' => now()->addYear(),
            'profile_photo' => 'test.jpg',
            'cnic_front' => 'test.jpg',
            'cnic_back' => 'test.jpg',
            'license_front' => 'test.jpg',
            'license_back' => 'test.jpg',
        ]);

        $vehicleType = VehicleType::create(['name' => uniqid('type-', true), 'is_active' => true]);
        Vehicle::create([
            'driver_id' => $driver->id,
            'vehicle_type_id' => $vehicleType->id,
            'brand' => 'Test',
            'model' => 'Car',
            'manufacture_year' => 2025,
            'color' => 'Blue',
            'registration_number' => uniqid('reg-', true),
            'engine_number' => uniqid('engine-', true),
            'chassis_number' => uniqid('chassis-', true),
            'total_seats' => 4,
            'available_seats' => 4,
            'vehicle_photo' => 'test.jpg',
            'registration_book' => 'book.jpg',
            'status' => 'approved',
        ]);

        return $driver;
    }
}
