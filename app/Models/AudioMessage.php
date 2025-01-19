<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AudioMessage extends Model
{

    const FOLDER_PATH = '/chat-files/audio-messages/';

    protected $guarded = [];
}
