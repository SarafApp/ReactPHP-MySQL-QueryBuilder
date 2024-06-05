<?php

namespace Saraf\QB\QueryBuilder\Helpers;

class QBHelper
{
    public static function getCurrentMicroTime(): float
    {
        return round(microtime(true) * 1000);
    }
}