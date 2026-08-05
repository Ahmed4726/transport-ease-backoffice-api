<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;

class CityController extends Controller
{
    public function index()
    {
        return City::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
