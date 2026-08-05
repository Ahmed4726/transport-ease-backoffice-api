<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Vehicle\StoreVehicleRequest;
use App\Http\Requests\API\Vehicle\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Services\Vehicle\VehicleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(
        private readonly VehicleService $vehicleService
    ) {}

    /**
     * Register Vehicle
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        try {

            $vehicle = $this->vehicleService->register(
                $request->validated(),
                auth()->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Vehicle registered successfully. Waiting for admin approval.',
                'data' => new VehicleResource($vehicle),
            ], 201);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        }
    }

    public function current(Request $request): JsonResponse
    {
        try {
            $vehicle = $this->vehicleService->getCurrentVehicle($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Current vehicle fetched successfully.',
                'data' => $vehicle ? new VehicleResource($vehicle) : null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        try {
            $updatedVehicle = $this->vehicleService->updateVehicle(
                $vehicle,
                $request->validated(),
                auth()->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Vehicle information submitted successfully. Waiting for admin review.',
                'data' => new VehicleResource($updatedVehicle),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
