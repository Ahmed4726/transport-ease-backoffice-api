<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\TripController;
use App\Models\City;
use App\Models\CityStop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class TripControllerSelectedStopNormalizationTest extends TestCase
{
    use RefreshDatabase;
    public function test_string_payload_is_normalized_to_integer_array(): void
    {
        $controller = new class extends TripController {
            public function __construct()
            {
            }
        };

        $method = new \ReflectionMethod($controller, 'normalizeSelectedStopIds');
        $method->setAccessible(true);

        $this->assertSame([1, 2, 3], $method->invoke($controller, '1, 2, 3'));
    }

    public function test_array_payload_is_filtered_to_integer_values(): void
    {
        $controller = new class extends TripController {
            public function __construct()
            {
            }
        };

        $method = new \ReflectionMethod($controller, 'normalizeSelectedStopIds');
        $method->setAccessible(true);

        $this->assertSame([4, 5], $method->invoke($controller, ['4', '5', 'abc']));
    }

    public function test_same_city_intermediate_stops_are_kept_when_not_endpoints(): void
    {
        $startCity = City::create(['name' => 'Karachi', 'province' => 'Sindh', 'latitude' => 24.8607, 'longitude' => 67.0011, 'is_active' => true]);
        $endCity = City::create(['name' => 'Lahore', 'province' => 'Punjab', 'latitude' => 31.5204, 'longitude' => 74.3587, 'is_active' => true]);

        $startStop = CityStop::create(['city_id' => $startCity->id, 'location_name' => 'Karachi Start', 'address' => 'Karachi start', 'latitude' => 24.8607, 'longitude' => 67.0011]);
        $endStop = CityStop::create(['city_id' => $endCity->id, 'location_name' => 'Lahore End', 'address' => 'Lahore end', 'latitude' => 31.5204, 'longitude' => 74.3587]);
        $startCityIntermediate = CityStop::create(['city_id' => $startCity->id, 'location_name' => 'Karachi Middle', 'address' => 'Karachi middle', 'latitude' => 25.0, 'longitude' => 67.1]);
        $endCityIntermediate = CityStop::create(['city_id' => $endCity->id, 'location_name' => 'Lahore Middle', 'address' => 'Lahore middle', 'latitude' => 31.3, 'longitude' => 74.2]);

        $controller = new class extends TripController {
            public function __construct()
            {
            }
        };

        $method = new \ReflectionMethod($controller, 'sanitizeIntermediateStops');
        $method->setAccessible(true);

        $result = $method->invoke($controller, [$startCityIntermediate->id, $endCityIntermediate->id], $startStop->id, $endStop->id);

        $this->assertEqualsCanonicalizing([$startCityIntermediate->id, $endCityIntermediate->id], $result);
    }
}
