<?php

namespace Tests\Unit;

use App\Enums\VehicleStatus;
use App\Http\Controllers\API\DriverTripController;
use App\Models\Vehicle;
use PHPUnit\Framework\TestCase;

class DriverTripSeatValidationTest extends TestCase
{
    public function test_approved_vehicle_rejects_seats_above_capacity(): void
    {
        $controller = new class extends DriverTripController {
        };

        $vehicle = new Vehicle([
            'status' => VehicleStatus::APPROVED,
            'available_seats' => 4,
        ]);

        $reflection = new \ReflectionMethod($controller, 'validateSeatCount');
        $reflection->setAccessible(true);
        $result = $reflection->invoke($controller, 5, $vehicle);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('greater than the approved vehicle capacity (4)', $result['message']);
    }

    public function test_non_approved_vehicle_requires_at_least_one_seat(): void
    {
        $controller = new class extends DriverTripController {
        };

        $vehicle = new Vehicle([
            'status' => VehicleStatus::PENDING,
            'available_seats' => 4,
        ]);

        $reflection = new \ReflectionMethod($controller, 'validateSeatCount');
        $reflection->setAccessible(true);
        $result = $reflection->invoke($controller, 0, $vehicle);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('at least 1', $result['message']);
    }
}
