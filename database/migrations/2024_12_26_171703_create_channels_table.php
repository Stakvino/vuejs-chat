<?php

use App\Models\Channel;
use App\Models\ChannelType;
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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ChannelType::class)->onDelete('cascade');
            $table->text('name')->default(Channel::DEFAULT_CHANNEL_NAME);
            $table->text('icon')->default(Channel::DEFAULT_CHANNEL_ICON);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
