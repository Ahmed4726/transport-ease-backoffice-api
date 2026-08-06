<?php

namespace Tests\Unit;

use App\Http\Controllers\API\DriverTripController;
use App\Models\DriverTrip;
use Illuminate\Support\Facades\Date;
use PHPUnit\Framework\TestCase;

class DriverTripStartStateTest extends TestCase
{
    public function test_starting_trip_marks_state_and_timestamp(): void
    {
        $controller = new class extends DriverTripController {
        };

        $trip = new DriverTrip([
            'status' => 'scheduled',
            'started_at' => null,
        ]);

        $reflection = new \ReflectionMethod($controller, 'applyTripStartState');
        $reflection->setAccessible(true);
        $result = $reflection->invoke($controller, $trip);

        $this->assertSame('started', $result->status);
        $this->assertNotNull($result->started_at);
    }
}
