<?php

namespace Database\Seeders;

use App\Models\DeviceModel;
use Illuminate\Database\Seeder;

class DeviceModelSeeder extends Seeder
{
    public function run(): void
    {
        DeviceModel::firstOrCreate(['type' => 'water']);
        DeviceModel::firstOrCreate(['type' => 'clock']);
    }
}
