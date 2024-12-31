<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
