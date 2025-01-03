<?php

namespace Database\Seeders;

use App\Models\ChannelType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ChannelTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ChannelType::insert([
            ['name' => 'public', 'created_at' => now()],
            ['name' => 'private', 'created_at' => now()],
        ]);
    }
}
