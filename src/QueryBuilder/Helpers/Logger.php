<?php

namespace Saraf\QB\QueryBuilder\Helpers;

class Logger
{
    public static function file($message): void
    {
        $filename = dirname(__FILE__) . '../../../logger.log';
        file_put_contents($filename, $message . PHP_EOL, FILE_APPEND);
    }

    public static function console($msg)
    {
        if (is_array($msg) || is_object($msg)) {
            echo "Logger: " . json_encode($msg, 128) . PHP_EOL;
        } else {
            echo "Logger: " . $msg . PHP_EOL;
        }
    }

    public static function dump($msg)
    {
        echo 'Dumping...' . PHP_EOL;
        var_dump($msg);
    }
}
