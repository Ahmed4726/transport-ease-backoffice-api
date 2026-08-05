<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [

            ['name'=>'Gujrat','province'=>'Punjab'],

            ['name'=>'Kharian','province'=>'Punjab'],

            ['name'=>'Lalamusa','province'=>'Punjab'],

            ['name'=>'Sara-e-Alamgir','province'=>'Punjab'],

            ['name'=>'Jhelum','province'=>'Punjab'],

            ['name'=>'Gujar Khan','province'=>'Punjab'],

            ['name'=>'Rawalpindi','province'=>'Punjab'],

            ['name'=>'Lahore','province'=>'Punjab'],

            ['name'=>'Kamoke','province'=>'Punjab'],

            ['name'=>'Gujranwala','province'=>'Punjab'],

            ['name'=>'Wazirabad','province'=>'Punjab'],

            ['name'=>'Islamabad','province'=>'ICT']
        ];

        foreach ($cities as $city) {

            City::updateOrCreate(

                ['name'=>$city['name']],

                $city

            );
        }
    }
}
