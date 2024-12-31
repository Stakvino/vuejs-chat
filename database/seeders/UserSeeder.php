<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Channel;
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
        User::factory()
        ->count(6)
        ->hasChannels(3)
        ->create();
        User::create([
            'name' => 'Ouss Dgun',
            'username' => 'oussdgun#0001',
            'avatar' => '',
            'personal_color' => '#5412AB',
            'email' => 'ousdgun@gmail.com',
            'email_verified_at' => '2024-12-26 18:27:00',
            'password' => Hash::make('password')
        ]);
    }
}
