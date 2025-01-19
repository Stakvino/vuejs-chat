<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileMessage extends Model
{

    const FOLDER_PATH = '/chat-files/attachments/';

    protected $guarded = [];
}
