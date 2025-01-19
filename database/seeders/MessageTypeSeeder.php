<?php

namespace Database\Seeders;

use App\Models\MessageType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MessageTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MessageType::insert([
            ['id' => MessageType::TEXT_ID, 'name' => 'text', 'created_at' => now()],
            ['id' => MessageType::FILE_ID, 'name' => 'file', 'created_at' => now()],
            ['id' => MessageType::AUDIO_ID, 'name' => 'audio', 'created_at' => now()],
        ]);
    }
}
