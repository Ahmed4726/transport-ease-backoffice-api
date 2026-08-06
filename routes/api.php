<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\VehicleController;
use App\Http\Controllers\API\VehicleTypeController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\CityController;
use App\Http\Controllers\API\RouteController;
use App\Http\Controllers\API\RouteStopController;
use App\Http\Controllers\API\DriverTripController;
use App\Http\Controllers\API\DriverTripLocationController;
use App\Http\Controllers\API\PassengerTripController;

Route::prefix('auth')->group(function () {

    // Public
    Route::post('/register/passenger', [AuthController::class, 'registerPassenger']);
    Route::post('/register/driver', [AuthController::class, 'registerDriver']);
    Route::post('/login', [AuthController::class, 'login']);

});

// Passenger-facing endpoints require authentication.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/cities/{city}/stops', [RouteStopController::class, 'cityStops']);
    Route::get('/passenger-trips', [PassengerTripController::class, 'index']);

    Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/profile/availability', [AuthController::class, 'updateAvailability']);
        Route::post('/profile/update', [AuthController::class, 'updateDriverProfile']);
        Route::post('/profile/personal-update', [AuthController::class, 'updatePersonalProfile']);
        Route::post('/application/resubmit', [AuthController::class, 'resubmitApplication']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/vehicle-types', [VehicleTypeController::class, 'index']);
        Route::get('/vehicles/current', [VehicleController::class, 'current']);
        Route::post('/vehicles', [VehicleController::class, 'store']);
        Route::post('/vehicles/{vehicle}/resubmit', [VehicleController::class, 'update']);

        // Routes & Stops
        Route::get('/routes', [RouteController::class, 'index']);
        Route::get('/routes/{route}/stops', [RouteController::class, 'stops']);
        Route::get('/routes/stops-between', [RouteController::class, 'stopsBetween']);

        // Admin stops CRUD
        Route::get('/stops', [RouteStopController::class, 'index']);
        Route::post('/stops', [RouteStopController::class, 'store']);
        Route::put('/stops/{stop}', [RouteStopController::class, 'update']);
        Route::delete('/stops/{stop}', [RouteStopController::class, 'destroy']);

        // Driver trips
        Route::get('/driver-trips', [DriverTripController::class, 'index']);
        Route::get('/driver-trips/{driverTrip}', [DriverTripController::class, 'show']);
        Route::put('/driver-trips/{driverTrip}', [DriverTripController::class, 'update']);
        Route::post('/driver-trips', [DriverTripController::class, 'store']);
        Route::post('/driver-trips/{driverTrip}/start', [DriverTripController::class, 'start']);
        Route::delete('/driver-trips/{driverTrip}', [DriverTripController::class, 'destroy']);
        Route::post('/driver-trips/{driverTrip}/locations', [DriverTripLocationController::class, 'store']);
        Route::get('/driver-trips/{driverTrip}/locations/latest', [DriverTripLocationController::class, 'latest']);

        Route::get('/me', [AuthController::class, 'me']);

});
