<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\ChannelType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Channel::create([
            'id' => 1,
            'channel_type_id' => ChannelType::PUBLIC_ID,
            'name' => 'Public Channel'
        ]);
        /*
        Channel::factory()
        ->count(20)
        ->hasMessages(60)
        ->create();
        */
    }
}
