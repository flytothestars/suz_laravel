<?php

namespace App\Enums;

class DayTimeEnum
{
    const BEFORE_NOON = 0;
    const AFTERNOON = 1;
    const DURING_DAY = 2;

    public static function description($time): string
    {
        switch ($time) {
            case self::BEFORE_NOON :
                return 'До обеда';
            case self::AFTERNOON:
                return 'После обеда';
            case self::DURING_DAY:
                return 'В течении дня';
            default:
                return '';
        }
    }
}
