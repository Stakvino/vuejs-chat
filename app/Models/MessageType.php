<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageType extends Model
{

    const TEXT_ID = 1;
    const FILE_ID = 2;
    const AUDIO_ID = 3;

    protected $guarded = [];

    public function isText()
    {
        return $this->id === MessageType::TEXT_ID;
    }

    public function isFile()
    {
        return $this->id === MessageType::FILE_ID;
    }

    public function isAudio()
    {
        return $this->id === MessageType::AUDIO_ID;
    }

}
