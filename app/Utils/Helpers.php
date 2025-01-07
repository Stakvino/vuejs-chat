<?php

namespace App\Utils;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class Helpers
{
    /**
     * format the create_at column so that its easly readable (6 hours | 2 weeks | 3 months).
     */
    public static function dateTimeFormat(Carbon $datetime): String
    {
        if ( $datetime->isToday() ) {
            return $datetime->format('h:i');
        }
        return $datetime->format('d/m/Y');
        /*
        return $datetime->diffForHumans(now(), [
            'options' => Carbon::JUST_NOW | Carbon::ONE_DAY_WORDS | 0,
            'syntax' => CarbonInterface::DIFF_ABSOLUTE
        ]);
        */
    }
}
