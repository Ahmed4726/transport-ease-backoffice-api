<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\RouteAdminController;
use App\Http\Controllers\Admin\RouteFareAdminController;
use App\Http\Controllers\Admin\RouteStopAdminController;
use App\Http\Controllers\API\VehicleController;
use App\Http\Controllers\Admin\VehicleController as AdminVehicleController;

use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'login'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'authenticate'])
        ->name('admin.authenticate');

});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('admin.logout');

    Route::prefix('drivers')
        ->name('admin.drivers.')
        ->group(function () {

            Route::get('/', [DriverController::class, 'index'])->name('index');

            Route::get('/{driver}', [DriverController::class, 'show'])->name('show');

            Route::post('/{driver}/approve', [DriverController::class, 'approve'])->name('approve');

            Route::post('/{driver}/reject', [DriverController::class, 'reject'])->name('reject');

        });


    Route::prefix('vehicles')
    ->name('admin.')
    ->group(function () {

        Route::get('/', [AdminVehicleController::class, 'index'])
            ->name('vehicles.index');

        Route::get('/{vehicle}', [AdminVehicleController::class, 'show'])
            ->name('vehicles.show');

        Route::post('/{vehicle}/approve', [AdminVehicleController::class, 'approve'])
            ->name('vehicles.approve');

        Route::post('/{vehicle}/reject', [AdminVehicleController::class, 'reject'])
            ->name('vehicles.reject');

    });

    Route::prefix('routes')
        ->name('admin.routes.')
        ->group(function () {

            Route::get('/', [RouteAdminController::class, 'index'])
                ->name('index');

            Route::get('/{route}', [RouteAdminController::class, 'show'])
                ->name('show');

            Route::post('/{route}/stops', [RouteStopAdminController::class, 'store'])
                ->name('stops.store');

            Route::get('/{route}/stops/{stop}/edit', [RouteStopAdminController::class, 'edit'])
                ->name('stops.edit');

            Route::put('/{route}/stops/{stop}', [RouteStopAdminController::class, 'update'])
                ->name('stops.update');

            Route::delete('/{route}/stops/{stop}', [RouteStopAdminController::class, 'destroy'])
                ->name('stops.destroy');

            Route::post('/{route}/fares', [RouteFareAdminController::class, 'store'])
                ->name('fares.store');

            Route::get('/{route}/fares/{fare}/edit', [RouteFareAdminController::class, 'edit'])
                ->name('fares.edit');

            Route::put('/{route}/fares/{fare}', [RouteFareAdminController::class, 'update'])
                ->name('fares.update');

            Route::delete('/{route}/fares/{fare}', [RouteFareAdminController::class, 'destroy'])
                ->name('fares.destroy');

        });

    Route::prefix('cities')
        ->name('admin.cities.')
        ->group(function () {

            Route::get('/', [CityController::class, 'index'])
                ->name('index');

            Route::get('/create', [CityController::class, 'create'])
                ->name('create');

            Route::post('/', [CityController::class, 'store'])
                ->name('store');

            Route::get('/{city}/edit', [CityController::class, 'edit'])
                ->name('edit');

            Route::put('/{city}', [CityController::class, 'update'])
                ->name('update');

            Route::delete('/{city}', [CityController::class, 'destroy'])
                ->name('destroy');

            // City stops management
            Route::prefix('/{city}/stops')
                ->name('stops.')
                ->group(function () {

                    Route::get('/', [\App\Http\Controllers\Admin\CityStopAdminController::class, 'index'])
                        ->name('index');

                    Route::get('/create', [\App\Http\Controllers\Admin\CityStopAdminController::class, 'create'])
                        ->name('create');

                    Route::post('/', [\App\Http\Controllers\Admin\CityStopAdminController::class, 'store'])
                        ->name('store');

                    Route::get('/{stop}/edit', [\App\Http\Controllers\Admin\CityStopAdminController::class, 'edit'])
                        ->name('edit');

                    Route::put('/{stop}', [\App\Http\Controllers\Admin\CityStopAdminController::class, 'update'])
                        ->name('update');

                    Route::delete('/{stop}', [\App\Http\Controllers\Admin\CityStopAdminController::class, 'destroy'])
                        ->name('destroy');

                });

        });

    });

