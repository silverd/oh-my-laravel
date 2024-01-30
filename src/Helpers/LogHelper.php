<?php

namespace Silverd\OhMyLaravel\Helpers;

use Monolog\Logger;
use Monolog\Handler\MongoDBHandler;
use Monolog\Formatter\MongoDBFormatter;
use Silverd\OhMyLaravel\Extensions\Logger\Handler\DatabaseHandler;

class LogHelper
{
    public static function makeMySQLDbChannels(array $channels)
    {
        $return = [];

        foreach ($channels as $channel) {
            $return[$channel] = [
                'driver'  => 'monolog',
                'handler' => DatabaseHandler::class,
                'with'    => [
                    'level'      => Logger::DEBUG,
                    'table'      => 'log_' . $channel,
                    'rotate'     => 'ymd',
                    'connection' => 'mysql_log',
                ],
            ];
        }

        return $return;
    }

    public static function makeMongoDbChannels(array $channels)
    {
        $return = [];

        foreach ($channels as $channel) {
            $return[$channel] = [
                'driver'    => 'monolog_log',
                'handler'   => MongoDBHandler::class,
                'formatter' => MongoDBFormatter::class,
                'with'      => [
                    'level'      => Logger::DEBUG,
                    'database'   => env('DB_LOG_DATABASE'),
                    'collection' => 'log_' . $channel,
                    'connection' => 'mongodb_log',
                ],
                'formatter_with' => [
                    'maxNestingLevel' => 10,
                ],
            ];
        }

        return $return;
    }
}
