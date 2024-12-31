<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelType extends Model
{
    const PUBLIC_ID = 1;
    const PRIVATE_ID = 2;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];


}
