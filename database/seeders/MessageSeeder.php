<?php

namespace Database\Seeders;

use App\Models\User;
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
        Message::factory()
        ->count(200)
        ->create();
        $hadi = User::where('email', 'hadisir@gmail.com')->first();
        Message::create([
            'text' => 'am here !',
            'channel_id' => 1,
            'user_id' => $hadi->id
        ]);
    }
}
