<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\TripController;
use PHPUnit\Framework\TestCase;

class TripControllerSelectedStopNormalizationTest extends TestCase
{
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
}
