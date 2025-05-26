<?php

use App\Models\User;
use App\Models\Channel;
use App\Models\MessageType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->foreignIdFor(Channel::class)->onDelete('cascade');
            $table->foreignIdFor(User::class)->onDelete('cascade');
            $table->foreignIdFor(MessageType::class)->onDelete('cascade');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
