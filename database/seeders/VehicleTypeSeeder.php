<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleType;

class VehicleTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [

            [
                'name' => 'Motorcycle',
                'icon' => 'motorcycle'
            ],

            [
                'name' => 'Rickshaw',
                'icon' => 'rickshaw'
            ],

            [
                'name' => 'Car',
                'icon' => 'car'
            ],

            [
                'name' => 'Sedan',
                'icon' => 'sedan'
            ],

            [
                'name' => 'SUV',
                'icon' => 'suv'
            ],

            [
                'name' => 'Pickup',
                'icon' => 'pickup'
            ],

            [
                'name' => 'Van',
                'icon' => 'van'
            ],

            [
                'name' => 'Mini Van',
                'icon' => 'minivan'
            ],

            [
                'name' => 'Hiace',
                'icon' => 'hiace'
            ],

            [
                'name' => 'Coaster',
                'icon' => 'coaster'
            ],

            [
                'name' => 'Bus',
                'icon' => 'bus'
            ],

        ];

        foreach ($types as $type) {

            VehicleType::updateOrCreate(

                ['name' => $type['name']],

                [
                    'icon' => $type['icon'],
                    'is_active' => true,
                ]

            );

        }
    }
}
