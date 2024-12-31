<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Message;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $channel = Channel::factory()->create();
        Message::factory()
        ->count(200)
        // ->for($channel)
        ->create();
    }
}
