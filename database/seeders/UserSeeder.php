<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Channel;
use App\Models\ChannelUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::create([
            'id' => User::CHAT_BOT_ID,
            'name' => 'Mr Robot',
            'username' => 'mrrobot#0001',
            'avatar' => 'robot.webp',
            'personal_color' => '#5412AB',
            'email' => 'mr@robot.com',
            'email_verified_at' => '2024-12-26 18:27:00',
            'password' => Hash::make('password')
        ]);

        User::insert([
            [
                'name' => 'Ouss Dgun',
                'username' => 'oussdgun#0002',
                'avatar' => '',
                'personal_color' => '#5412AB',
                'email' => 'ousdgun@gmail.com',
                'email_verified_at' => '2024-12-26 18:27:00',
                'password' => Hash::make('password')
            ],
            [
                'name' => 'Hadi Sir',
                'username' => 'hadisir#0003',
                'avatar' => '',
                'personal_color' => '#3190AA',
                'email' => 'hadisir@gmail.com',
                'email_verified_at' => '2024-12-26 18:27:00',
                'password' => Hash::make('password')
            ]
        ]);

        User::factory()
        ->count(6)
        // ->hasChannels(3)
        ->create();

        $allUsersIds = User::all()->pluck('id');
        ChannelUser::insert(
            $allUsersIds->map(function($userId) {
                return [
                    'user_id' => $userId,
                    'channel_id' => Channel::PUBLIC_CHANNEL_ID
                ];
            })->toArray()
        );
    }
}
