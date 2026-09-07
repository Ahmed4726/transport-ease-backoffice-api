<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\City;
use App\Models\CityStop;
use App\Models\Driver;
use App\Models\DriverTrip;
use App\Models\DriverTripStop;
use App\Models\Passenger;
use App\Models\TripBooking;
use App\Models\TripFare;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_passenger_can_book_one_or_multiple_seats(): void
    {
        [$user] = $this->passenger('one');
        $trip = $this->trip(5);
        $stopIds = $trip->stops()->orderBy('stop_order')->pluck('route_stop_id')->all();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/passenger/bookings', ['trip_id' => $trip->id, 'from_stop_id' => $stopIds[0], 'to_stop_id' => $stopIds[1], 'seats' => 3])
            ->assertCreated()
            ->assertJsonPath('data.seats', 3)
            ->assertJsonPath('data.fare_per_seat', '500.00')
            ->assertJsonPath('data.total_fare', '1500.00')
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertDatabaseHas('trip_bookings', ['driver_trip_id' => $trip->id, 'seats' => 3, 'status' => 'confirmed']);
    }

    public function test_booking_seats_must_be_between_one_and_four(): void
    {
        [$user] = $this->passenger('limits');
        $trip = $this->trip();

        $this->actingAs($user, 'sanctum')->postJson('/api/passenger/bookings', ['trip_id' => $trip->id, 'seats' => 0])->assertUnprocessable();
        $this->actingAs($user, 'sanctum')->postJson('/api/passenger/bookings', ['trip_id' => $trip->id, 'seats' => 5])->assertUnprocessable();
    }

    public function test_booking_rejects_missing_fare_and_invalid_stop_journeys(): void
    {
        [$user] = $this->passenger('fare-validation');
        $trip = $this->trip();
        $stopIds = $trip->stops()->orderBy('stop_order')->pluck('route_stop_id')->all();
        $otherStop = CityStop::create(['city_id' => $trip->stops()->first()->stop->city_id, 'location_name' => 'Other', 'address' => 'Other', 'latitude' => 24.2, 'longitude' => 67.2]);

        $this->actingAs($user, 'sanctum')->postJson('/api/passenger/bookings', ['trip_id' => $trip->id, 'from_stop_id' => $stopIds[1], 'to_stop_id' => $stopIds[0], 'seats' => 1])->assertUnprocessable();
        $this->actingAs($user, 'sanctum')->postJson('/api/passenger/bookings', ['trip_id' => $trip->id, 'from_stop_id' => $stopIds[0], 'to_stop_id' => $stopIds[0], 'seats' => 1])->assertUnprocessable();
        $this->actingAs($user, 'sanctum')->postJson('/api/passenger/bookings', ['trip_id' => $trip->id, 'from_stop_id' => $otherStop->id, 'to_stop_id' => $stopIds[1], 'seats' => 1])->assertUnprocessable();

        $trip->fares()->delete();
        $this->actingAs($user, 'sanctum')->postJson('/api/passenger/bookings', ['trip_id' => $trip->id, 'from_stop_id' => $stopIds[0], 'to_stop_id' => $stopIds[1], 'seats' => 1])->assertUnprocessable();
        $this->assertDatabaseCount('trip_bookings', 0);
    }

    public function test_booking_ignores_client_fare_and_preserves_the_snapshot_after_trip_fare_changes(): void
    {
        [$user] = $this->passenger('fare-snapshot');
        $trip = $this->trip();
        $stopIds = $trip->stops()->orderBy('stop_order')->pluck('route_stop_id')->all();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/passenger/bookings', [
                'trip_id' => $trip->id,
                'from_stop_id' => $stopIds[0],
                'to_stop_id' => $stopIds[1],
                'seats' => 2,
                'fare_per_seat' => 1,
                'total_fare' => 2,
            ])
            ->assertCreated()
            ->assertJsonPath('data.fare_per_seat', '500.00')
            ->assertJsonPath('data.total_fare', '1000.00');

        $trip->fares()->update(['fare' => 700]);
        $booking = TripBooking::first();
        $this->assertSame('500.00', $booking->fare_per_seat);
        $this->assertSame('1000.00', $booking->total_fare);
    }

    public function test_booking_cannot_exceed_real_availability_or_duplicate_an_active_booking(): void
    {
        [$firstUser, $firstPassenger] = $this->passenger('first');
        [$secondUser] = $this->passenger('second');
        $trip = $this->trip(3);
        TripBooking::create(['driver_trip_id' => $trip->id, 'passenger_id' => $firstPassenger->id, 'seats' => 2, 'status' => BookingStatus::CONFIRMED, 'booking_reference' => 'TE-EXIST01']);
        $stopIds = $trip->stops()->orderBy('stop_order')->pluck('route_stop_id')->all();

        $this->actingAs($secondUser, 'sanctum')->postJson('/api/passenger/bookings', ['trip_id' => $trip->id, 'from_stop_id' => $stopIds[0], 'to_stop_id' => $stopIds[1], 'seats' => 2])->assertUnprocessable();
        $this->actingAs($firstUser, 'sanctum')->postJson('/api/passenger/bookings', ['trip_id' => $trip->id, 'from_stop_id' => $stopIds[0], 'to_stop_id' => $stopIds[1], 'seats' => 1])->assertUnprocessable();
    }

    public function test_cancelled_booking_releases_seats_and_allows_a_new_booking(): void
    {
        [$user] = $this->passenger('cancel');
        $trip = $this->trip(2);
        $booking = $this->book($user, $trip, 2);

        $this->actingAs($user, 'sanctum')->postJson("/api/passenger/bookings/{$booking->id}/cancel")->assertOk();
        $stopIds = $trip->stops()->orderBy('stop_order')->pluck('route_stop_id')->all();
        $this->actingAs($user, 'sanctum')->postJson('/api/passenger/bookings', ['trip_id' => $trip->id, 'from_stop_id' => $stopIds[0], 'to_stop_id' => $stopIds[1], 'seats' => 2])->assertCreated();

        $this->assertDatabaseHas('trip_bookings', ['id' => $booking->id, 'status' => 'cancelled']);
    }

    public function test_passenger_cannot_cancel_after_trip_starts_or_access_another_passengers_booking(): void
    {
        [$firstUser] = $this->passenger('owner');
        [$secondUser] = $this->passenger('other');
        $trip = $this->trip();
        $booking = $this->book($firstUser, $trip);

        $this->actingAs($secondUser, 'sanctum')->getJson("/api/passenger/bookings/{$booking->id}")->assertForbidden();
        $this->actingAs($secondUser, 'sanctum')->postJson("/api/passenger/bookings/{$booking->id}/cancel")->assertForbidden();

        $trip->update(['status' => 'started', 'started_at' => now()]);
        $this->actingAs($firstUser, 'sanctum')->postJson("/api/passenger/bookings/{$booking->id}/cancel")->assertUnprocessable();
    }

    public function test_only_owning_driver_can_view_active_trip_bookings(): void
    {
        [$passengerUser] = $this->passenger('manifest');
        [$driverUser] = $this->driver('owner-driver');
        [$otherDriverUser] = $this->driver('other-driver');
        $trip = $this->trip(4, $driverUser->driver);
        $this->book($passengerUser, $trip, 2);

        $this->actingAs($driverUser, 'sanctum')->getJson("/api/driver-trips/{$trip->id}/bookings")
            ->assertOk()
            ->assertJsonPath('data.bookings.0.passenger.name', $passengerUser->name)
            ->assertJsonMissing(['email' => $passengerUser->email])
            ->assertJsonMissing(['phone' => $passengerUser->phone])
            ->assertJsonPath('data.trip.total_capacity', 4)
            ->assertJsonPath('data.trip.booked_seats', 2)
            ->assertJsonPath('data.trip.available_seats', 2);

        $this->actingAs($otherDriverUser, 'sanctum')->getJson("/api/driver-trips/{$trip->id}/bookings")->assertForbidden();
    }

    public function test_manifest_requires_an_approved_driver_and_returns_empty_data_for_empty_trips(): void
    {
        [, $driver] = $this->driver('empty-manifest');
        $trip = $this->trip(4, $driver);

        $this->getJson("/api/driver-trips/{$trip->id}/bookings")->assertUnauthorized();

        [$passengerUser] = $this->passenger('manifest-passenger');
        $this->actingAs($passengerUser, 'sanctum')->getJson("/api/driver-trips/{$trip->id}/bookings")->assertForbidden();

        $driver->user->update(['status' => UserStatus::PENDING]);
        $this->actingAs($driver->user, 'sanctum')->getJson("/api/driver-trips/{$trip->id}/bookings")->assertForbidden();

        $driver->user->update(['status' => UserStatus::REJECTED]);
        $this->actingAs($driver->user, 'sanctum')->getJson("/api/driver-trips/{$trip->id}/bookings")->assertForbidden();

        $driver->user->update(['status' => UserStatus::APPROVED]);
        $this->actingAs($driver->user, 'sanctum')
            ->getJson("/api/driver-trips/{$trip->id}/bookings")
            ->assertOk()
            ->assertJsonPath('data.bookings', [])
            ->assertJsonPath('data.trip.booked_seats', 0)
            ->assertJsonPath('data.trip.available_seats', 4);
    }

    public function test_manifest_returns_all_statuses_but_only_active_seats_are_booked(): void
    {
        [, $driver] = $this->driver('status-manifest-driver');
        $trip = $this->trip(10, $driver);
        $statuses = [BookingStatus::CONFIRMED, BookingStatus::BOARDED, BookingStatus::CANCELLED, BookingStatus::COMPLETED, BookingStatus::NO_SHOW];

        foreach ($statuses as $index => $status) {
            [, $passenger] = $this->passenger("status-manifest-{$index}");
            TripBooking::create([
                'driver_trip_id' => $trip->id,
                'passenger_id' => $passenger->id,
                'seats' => 1,
                'status' => $status,
                'booking_reference' => 'TE-STATUS'.$index,
            ]);
        }

        $this->actingAs($driver->user, 'sanctum')
            ->getJson("/api/driver-trips/{$trip->id}/bookings")
            ->assertOk()
            ->assertJsonCount(5, 'data.bookings')
            ->assertJsonPath('data.trip.booked_seats', 2)
            ->assertJsonPath('data.trip.available_seats', 8)
            ->assertJsonMissingPath('data.bookings.0.passenger.email')
            ->assertJsonMissingPath('data.bookings.0.passenger.phone');
    }

    public function test_approved_owner_can_board_a_confirmed_booking_after_trip_starts(): void
    {
        [$passengerUser] = $this->passenger('board-owner-passenger');
        [$driverUser] = $this->driver('board-owner-driver');
        $trip = $this->trip(4, $driverUser->driver);
        $booking = $this->book($passengerUser, $trip, 2);
        $trip->update(['status' => 'started', 'started_at' => now()]);

        $this->actingAs($driverUser, 'sanctum')
            ->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/board")
            ->assertOk()
            ->assertJsonPath('data.status', 'boarded')
            ->assertJsonPath('data.seats', 2)
            ->assertJsonPath('data.passenger.name', $passengerUser->name)
            ->assertJsonMissing(['email' => $passengerUser->email])
            ->assertJsonMissing(['phone' => $passengerUser->phone]);

        $this->assertDatabaseHas('trip_bookings', ['id' => $booking->id, 'status' => 'boarded']);
    }

    public function test_boarding_requires_an_approved_owner_and_matching_trip_booking(): void
    {
        [$passengerUser] = $this->passenger('board-security-passenger');
        [$driverUser] = $this->driver('board-security-driver');
        [$otherDriverUser] = $this->driver('board-other-driver');
        $trip = $this->trip(4, $driverUser->driver);
        $otherTrip = $this->trip(4, $otherDriverUser->driver);
        $booking = $this->book($passengerUser, $trip);
        $otherBooking = $this->book($passengerUser, $otherTrip);
        $trip->update(['status' => 'started', 'started_at' => now()]);

        $this->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/board")->assertUnauthorized();
        $this->actingAs($passengerUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/board")->assertForbidden();
        $this->actingAs($otherDriverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/board")->assertForbidden();
        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$otherBooking->id}/board")->assertUnprocessable();

        $driverUser->update(['status' => UserStatus::PENDING]);
        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/board")->assertForbidden();
    }

    public function test_boarding_rejects_invalid_statuses_duplicate_requests_and_scheduled_trips(): void
    {
        [$driverUser] = $this->driver('board-states-driver');
        $trip = $this->trip(10, $driverUser->driver);
        $passengers = [];
        foreach ([BookingStatus::CANCELLED, BookingStatus::BOARDED, BookingStatus::COMPLETED, BookingStatus::NO_SHOW] as $index => $status) {
            [, $passenger] = $this->passenger("board-state-{$index}");
            $passengers[] = TripBooking::create([
                'driver_trip_id' => $trip->id,
                'passenger_id' => $passenger->id,
                'seats' => 1,
                'status' => $status,
                'booking_reference' => 'TE-BOARD'.$index,
            ]);
        }
        [$confirmedUser] = $this->passenger('board-confirmed');
        $confirmed = $this->book($confirmedUser, $trip);

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$confirmed->id}/board")->assertUnprocessable();
        $trip->update(['status' => 'started', 'started_at' => now()]);

        foreach ($passengers as $booking) {
            $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/board")->assertUnprocessable();
        }

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$confirmed->id}/board")->assertOk();
        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$confirmed->id}/board")->assertUnprocessable();
    }

    public function test_boarding_does_not_change_seat_occupancy(): void
    {
        [$driverUser] = $this->driver('board-seats-driver');
        [$firstUser] = $this->passenger('board-seats-first');
        [$secondUser] = $this->passenger('board-seats-second');
        $trip = $this->trip(4, $driverUser->driver);
        $firstBooking = $this->book($firstUser, $trip, 2);
        $this->book($secondUser, $trip, 1);
        $trip->update(['status' => 'started', 'started_at' => now()]);

        $service = app(BookingService::class);
        $this->assertSame(3, $service->reservedSeats($trip->fresh()));
        $this->assertSame(1, $service->availableSeats($trip->fresh()));

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$firstBooking->id}/board")->assertOk();

        $this->assertSame(3, $service->reservedSeats($trip->fresh()));
        $this->assertSame(1, $service->availableSeats($trip->fresh()));
    }

    public function test_approved_driver_can_mark_confirmed_booking_no_show_and_release_seats(): void
    {
        [$passengerUser] = $this->passenger('no-show-passenger');
        [$driverUser] = $this->driver('no-show-driver');
        $trip = $this->trip(4, $driverUser->driver);
        $booking = $this->book($passengerUser, $trip, 2);
        $trip->update(['status' => 'started', 'started_at' => now()]);

        $this->actingAs($driverUser, 'sanctum')
            ->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/no-show")
            ->assertOk()
            ->assertJsonPath('data.status', 'no_show')
            ->assertJsonPath('data.seats', 2)
            ->assertJsonPath('data.passenger.name', $passengerUser->name)
            ->assertJsonMissing(['email' => $passengerUser->email])
            ->assertJsonMissing(['phone' => $passengerUser->phone]);

        $this->assertDatabaseHas('trip_bookings', ['id' => $booking->id, 'status' => 'no_show']);
        $this->assertNotNull($booking->fresh()->no_show_at);
        $service = app(BookingService::class);
        $this->assertSame(0, $service->reservedSeats($trip->fresh()));
        $this->assertSame(4, $service->availableSeats($trip->fresh()));
        $this->assertSame(4, $trip->fresh()->available_seats);
    }

    public function test_no_show_requires_started_trip_approved_owner_and_matching_booking(): void
    {
        [$passengerUser] = $this->passenger('no-show-security-passenger');
        [$driverUser] = $this->driver('no-show-security-driver');
        [$otherDriverUser] = $this->driver('no-show-other-driver');
        $trip = $this->trip(4, $driverUser->driver);
        $otherTrip = $this->trip(4, $otherDriverUser->driver);
        $booking = $this->book($passengerUser, $trip);
        $otherBooking = $this->book($passengerUser, $otherTrip);

        $this->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/no-show")->assertUnauthorized();
        $this->actingAs($passengerUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/no-show")->assertForbidden();
        $this->actingAs($otherDriverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/no-show")->assertForbidden();
        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$otherBooking->id}/no-show")->assertUnprocessable();

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/no-show")->assertUnprocessable();
        $driverUser->update(['status' => UserStatus::PENDING]);
        $trip->update(['status' => 'started', 'started_at' => now()]);
        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/no-show")->assertForbidden();
    }

    public function test_no_show_rejects_boarded_cancelled_completed_and_existing_no_show_bookings(): void
    {
        [$driverUser] = $this->driver('no-show-states-driver');
        $trip = $this->trip(10, $driverUser->driver);
        $bookings = [];
        foreach ([BookingStatus::BOARDED, BookingStatus::CANCELLED, BookingStatus::COMPLETED, BookingStatus::NO_SHOW] as $index => $status) {
            [, $passenger] = $this->passenger("no-show-state-{$index}");
            $bookings[] = TripBooking::create([
                'driver_trip_id' => $trip->id,
                'passenger_id' => $passenger->id,
                'seats' => 1,
                'status' => $status,
                'booking_reference' => 'TE-NOSTATE'.$index,
            ]);
        }
        $trip->update(['status' => 'started', 'started_at' => now()]);

        foreach ($bookings as $booking) {
            $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/no-show")->assertUnprocessable();
        }
    }

    public function test_no_show_cannot_be_applied_to_completed_or_cancelled_trips_and_duplicate_is_rejected(): void
    {
        [$driverUser] = $this->driver('no-show-trip-states');
        $trip = $this->trip(4, $driverUser->driver);
        [$passengerUser] = $this->passenger('no-show-trip-state-passenger');
        $booking = $this->book($passengerUser, $trip);

        foreach (['scheduled', 'completed', 'cancelled'] as $status) {
            $trip->update(['status' => $status]);
            $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/no-show")->assertUnprocessable();
        }

        $trip->update(['status' => 'started', 'started_at' => now()]);
        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/no-show")->assertOk();
        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/bookings/{$booking->id}/no-show")->assertUnprocessable();
    }

    public function test_started_trip_completion_finalizes_bookings_and_populates_timestamps(): void
    {
        [$driverUser] = $this->driver('complete-driver');
        [$boardedUser] = $this->passenger('complete-boarded');
        [$confirmedUser] = $this->passenger('complete-confirmed');
        [$noShowUser] = $this->passenger('complete-no-show');
        [$cancelledUser] = $this->passenger('complete-cancelled');
        $trip = $this->trip(10, $driverUser->driver);
        $boarded = $this->book($boardedUser, $trip, 2);
        $boarded->update(['status' => BookingStatus::BOARDED, 'boarded_at' => now()]);
        $confirmed = $this->book($confirmedUser, $trip, 2);
        $noShow = TripBooking::create(['driver_trip_id' => $trip->id, 'passenger_id' => $noShowUser->passenger->id, 'seats' => 1, 'status' => BookingStatus::NO_SHOW, 'no_show_at' => now(), 'booking_reference' => 'TE-COMPNOSHOW']);
        $cancelled = TripBooking::create(['driver_trip_id' => $trip->id, 'passenger_id' => $cancelledUser->passenger->id, 'seats' => 1, 'status' => BookingStatus::CANCELLED, 'cancelled_at' => now(), 'booking_reference' => 'TE-COMPCANCEL']);
        $existingNoShowAt = $noShow->no_show_at;
        $trip->update(['status' => 'started', 'started_at' => now()]);

        $this->actingAs($driverUser, 'sanctum')
            ->postJson("/api/driver-trips/{$trip->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertNotNull($trip->fresh()->completed_at);
        $this->assertSame('completed', $boarded->fresh()->status->value);
        $this->assertNotNull($boarded->fresh()->completed_at);
        $this->assertSame('no_show', $confirmed->fresh()->status->value);
        $this->assertNotNull($confirmed->fresh()->no_show_at);
        $this->assertSame('no_show', $noShow->fresh()->status->value);
        $this->assertEquals($existingNoShowAt, $noShow->fresh()->no_show_at);
        $this->assertSame('cancelled', $cancelled->fresh()->status->value);
        $this->assertSame(0, app(BookingService::class)->reservedSeats($trip->fresh()));
        $this->assertSame(10, app(BookingService::class)->availableSeats($trip->fresh()));
        $this->assertSame(10, $trip->fresh()->available_seats);
    }

    public function test_trip_completion_requires_the_owning_approved_driver_and_started_state(): void
    {
        [$driverUser] = $this->driver('complete-owner');
        [$otherDriverUser] = $this->driver('complete-other');
        $trip = $this->trip(4, $driverUser->driver);

        $this->postJson("/api/driver-trips/{$trip->id}/complete")->assertUnauthorized();
        $this->actingAs($otherDriverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/complete")->assertForbidden();
        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/complete")->assertUnprocessable();

        foreach (['completed', 'cancelled'] as $status) {
            $trip->update(['status' => $status]);
            $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/complete")->assertUnprocessable();
        }

        $trip->update(['status' => 'started', 'started_at' => now()]);
        $driverUser->update(['status' => UserStatus::PENDING]);
        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/complete")->assertForbidden();
    }

    public function test_trip_completion_is_rejected_on_a_second_request(): void
    {
        [$driverUser] = $this->driver('complete-duplicate');
        $trip = $this->trip(4, $driverUser->driver);
        $trip->update(['status' => 'started', 'started_at' => now()]);

        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/complete")->assertOk();
        $completedAt = $trip->fresh()->completed_at;
        $this->actingAs($driverUser, 'sanctum')->postJson("/api/driver-trips/{$trip->id}/complete")->assertUnprocessable();
        $this->assertEquals($completedAt, $trip->fresh()->completed_at);
    }

    public function test_trip_start_is_atomic_and_does_not_rewrite_started_timestamp(): void
    {
        [$driverUser] = $this->driver('start-atomic');
        $trip = $this->trip(4, $driverUser->driver);

        $this->actingAs($driverUser, 'sanctum')
            ->postJson("/api/driver-trips/{$trip->id}/start")
            ->assertOk()
            ->assertJsonPath('data.status', 'started');

        $startedAt = $trip->fresh()->started_at;
        $this->actingAs($driverUser, 'sanctum')
            ->postJson("/api/driver-trips/{$trip->id}/start")
            ->assertUnprocessable();

        $this->assertEquals($startedAt, $trip->fresh()->started_at);
    }

    public function test_trip_capacity_cannot_be_reduced_below_active_bookings(): void
    {
        [$driverUser] = $this->driver('capacity-hardening');
        [$passengerUser] = $this->passenger('capacity-passenger');
        $trip = $this->trip(4, $driverUser->driver);
        $this->book($passengerUser, $trip, 3);

        $this->actingAs($driverUser, 'sanctum')
            ->putJson("/api/driver-trips/{$trip->id}", ['total_seats' => 2])
            ->assertUnprocessable();

        $this->assertSame(4, $trip->fresh()->total_seats);
        $this->assertSame(1, $trip->fresh()->available_seats);
    }

    public function test_driver_trip_history_filters_owned_trips_and_completed_trips_are_read_only(): void
    {
        [$driverUser] = $this->driver('history-owner');
        [, $otherDriver] = $this->driver('history-other');
        $scheduled = $this->trip(4, $driverUser->driver);
        $started = $this->trip(4, $driverUser->driver);
        $started->update(['status' => 'started', 'started_at' => now()]);
        $completed = $this->trip(4, $driverUser->driver);
        $completed->update(['status' => 'completed', 'completed_at' => now()]);
        $this->trip(4, $otherDriver);

        $this->actingAs($driverUser, 'sanctum')
            ->getJson('/api/driver-trips?status=completed')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $completed->id);

        $this->actingAs($driverUser, 'sanctum')
            ->putJson("/api/driver-trips/{$completed->id}", ['total_seats' => 3])
            ->assertUnprocessable();

        $this->actingAs($driverUser, 'sanctum')
            ->deleteJson("/api/driver-trips/{$completed->id}")
            ->assertUnprocessable();

        $this->assertDatabaseHas('driver_trips', ['id' => $scheduled->id, 'status' => 'scheduled']);
        $this->assertDatabaseHas('driver_trips', ['id' => $started->id, 'status' => 'started']);
    }

    public function test_unauthenticated_or_non_bookable_trip_cannot_be_booked(): void
    {
        [$user] = $this->passenger('state');
        $trip = $this->trip();
        $stopIds = $trip->stops()->orderBy('stop_order')->pluck('route_stop_id')->all();

        $this->postJson('/api/passenger/bookings', ['trip_id' => $trip->id, 'from_stop_id' => $stopIds[0], 'to_stop_id' => $stopIds[1], 'seats' => 1])->assertUnauthorized();
        $trip->update(['status' => 'started']);
        $this->actingAs($user, 'sanctum')->postJson('/api/passenger/bookings', ['trip_id' => $trip->id, 'from_stop_id' => $stopIds[0], 'to_stop_id' => $stopIds[1], 'seats' => 1])->assertUnprocessable();
    }

    public function test_trip_search_uses_booking_availability_and_cancellation_restores_it(): void
    {
        [$user] = $this->passenger('search');
        $trip = $this->trip(4);
        $booking = $this->book($user, $trip, 3);
        $stopIds = $trip->stops()->orderBy('stop_order')->pluck('route_stop_id')->all();

        $this->actingAs($user, 'sanctum')->getJson('/api/passenger-trips?from_stop_id='.$stopIds[0].'&to_stop_id='.$stopIds[1])
            ->assertOk()
            ->assertJsonPath('data.0.available_seats', 1)
            ->assertJsonPath('data.0.seats_booked', 3);

        $this->actingAs($user, 'sanctum')->postJson("/api/passenger/bookings/{$booking->id}/cancel")->assertOk();
        $this->actingAs($user, 'sanctum')->getJson('/api/passenger-trips?from_stop_id='.$stopIds[0].'&to_stop_id='.$stopIds[1])
            ->assertJsonPath('data.0.available_seats', 4)
            ->assertJsonPath('data.0.seats_booked', 0);
    }

    public function test_availability_is_rechecked_for_each_reservation_attempt(): void
    {
        [$firstUser] = $this->passenger('lock-first');
        [$secondUser] = $this->passenger('lock-second');
        $trip = $this->trip(2);
        $service = app(BookingService::class);

        $service->create($firstUser->passenger, $trip->id, 2);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->create($secondUser->passenger, $trip->id, 1);
    }

    public function test_historical_booking_statuses_do_not_consume_seats(): void
    {
        [$user] = $this->passenger('historical');
        $trip = $this->trip(4);
        $booking = $this->book($user, $trip, 3);

        $booking->update(['status' => BookingStatus::BOARDED]);
        $booking->update(['status' => BookingStatus::COMPLETED]);

        $this->assertSame(4, app(BookingService::class)->availableSeats($trip->fresh()));
    }

    public function test_terminal_booking_statuses_cannot_be_reactivated_or_cancelled(): void
    {
        [$user] = $this->passenger('terminal');
        $trip = $this->trip();
        $booking = $this->book($user, $trip);
        $booking->update(['status' => BookingStatus::NO_SHOW]);

        $this->expectException(\InvalidArgumentException::class);
        $booking->update(['status' => BookingStatus::CONFIRMED]);
    }

    public function test_unapproved_driver_cannot_access_a_trip_manifest(): void
    {
        [$passengerUser] = $this->passenger('unapproved-manifest');
        [$driverUser] = $this->driver('unapproved-manifest');
        $trip = $this->trip(4, $driverUser->driver);
        $this->book($passengerUser, $trip);
        $driverUser->update(['status' => UserStatus::PENDING]);

        $this->actingAs($driverUser, 'sanctum')
            ->getJson("/api/driver-trips/{$trip->id}/bookings")
            ->assertForbidden();
    }

    public function test_driver_cannot_create_a_trip_with_another_drivers_vehicle(): void
    {
        [$driverUser] = $this->driver('vehicle-owner');
        [, $otherDriver] = $this->driver('vehicle-other');
        $otherTrip = $this->trip(4, $otherDriver);
        $stopIds = $otherTrip->stops()->orderBy('stop_order')->pluck('route_stop_id')->all();

        $this->actingAs($driverUser, 'sanctum')
            ->postJson('/api/driver-trips', [
                'vehicle_id' => $otherTrip->vehicle_id,
                'total_seats' => 4,
                'from_stop_id' => $stopIds[0],
                'to_stop_id' => $stopIds[1],
            ])
            ->assertUnprocessable();
    }

    private function book(User $user, DriverTrip $trip, int $seats = 1): TripBooking
    {
        return app(BookingService::class)->create($user->passenger, $trip->id, $seats);
    }

    private function passenger(string $suffix): array
    {
        $user = User::create([
            'name' => "Passenger {$suffix}", 'email' => "{$suffix}@passenger.test", 'phone' => '03'.str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT),
            'password' => 'password', 'role' => UserRole::PASSENGER, 'status' => UserStatus::APPROVED,
        ]);

        return [$user, Passenger::create(['user_id' => $user->id])];
    }

    private function driver(string $suffix): array
    {
        $user = User::create([
            'name' => "Driver {$suffix}", 'email' => "{$suffix}@driver.test", 'phone' => '04'.str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT),
            'password' => 'password', 'role' => UserRole::DRIVER, 'status' => UserStatus::APPROVED,
        ]);
        $driver = Driver::create([
            'user_id' => $user->id, 'cnic' => 'C'.random_int(1000000, 9999999), 'license_number' => 'L'.random_int(1000000, 9999999), 'license_expiry' => now()->addYear(),
            'profile_photo' => 'test.jpg', 'cnic_front' => 'test.jpg', 'cnic_back' => 'test.jpg', 'license_front' => 'test.jpg', 'license_back' => 'test.jpg',
        ]);
        $user->setRelation('driver', $driver);

        return [$user, $driver];
    }

    private function trip(int $seats = 5, ?Driver $driver = null): DriverTrip
    {
        $driver ??= $this->driver('trip-'.random_int(1000, 9999))[1];
        $vehicleType = VehicleType::create(['name' => 'Car '.random_int(1000, 9999), 'is_active' => true]);
        $vehicle = Vehicle::create([
            'driver_id' => $driver->id, 'vehicle_type_id' => $vehicleType->id, 'brand' => 'Test', 'model' => 'Car', 'manufacture_year' => 2025, 'color' => 'Blue',
            'registration_number' => 'REG'.random_int(100000, 999999), 'engine_number' => 'ENG'.random_int(100000, 999999), 'chassis_number' => 'CHS'.random_int(100000, 999999),
            'total_seats' => $seats, 'available_seats' => $seats, 'vehicle_photo' => 'test.jpg', 'registration_book' => 'book.jpg', 'status' => 'approved',
        ]);
        $trip = DriverTrip::create(['driver_id' => $driver->id, 'vehicle_id' => $vehicle->id, 'trip_date' => now()->toDateString(), 'departure_time' => '10:00', 'total_seats' => $seats, 'available_seats' => $seats, 'status' => 'scheduled']);
        $city = City::create(['name' => 'City '.random_int(1000, 9999), 'province' => 'Test', 'latitude' => 24.0, 'longitude' => 67.0, 'is_active' => true]);
        $from = CityStop::create(['city_id' => $city->id, 'location_name' => 'From', 'address' => 'From', 'latitude' => 24.0, 'longitude' => 67.0]);
        $to = CityStop::create(['city_id' => $city->id, 'location_name' => 'To', 'address' => 'To', 'latitude' => 24.1, 'longitude' => 67.1]);
        DriverTripStop::create(['driver_trip_id' => $trip->id, 'route_stop_id' => $from->id, 'stop_order' => 1]);
        DriverTripStop::create(['driver_trip_id' => $trip->id, 'route_stop_id' => $to->id, 'stop_order' => 2]);
        TripFare::create(['driver_trip_id' => $trip->id, 'from_stop_id' => $from->id, 'to_stop_id' => $to->id, 'fare' => 500]);

        return $trip;
    }
}
