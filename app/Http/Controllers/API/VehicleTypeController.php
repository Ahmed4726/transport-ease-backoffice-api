<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleTypeResource;
use App\Models\VehicleType;

class VehicleTypeController extends Controller
{
    public function index()
    {
        return VehicleTypeResource::collection(
            VehicleType::orderBy('name')->get()
        );
    }
}
