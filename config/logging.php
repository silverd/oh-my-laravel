<?php

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Handler\RedisHandler;
use Monolog\Handler\SwiftMailerHandler;
use Silverd\OhMyLaravel\Extensions\Logger\DatabaseHandler;
use Silverd\OhMyLaravel\Extensions\Logger\WorkWechatGroupRobotHandler;

$return = [

    // 运维邮件组
    'alarm_email' => env('LOG_MAIL_TO'),

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', env('LOG_STACK_CHANNELS')) ?: ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel_' . php_sapi_name() . '.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel_' . php_sapi_name() . '.log'),
            'level' => 'debug',
            'days' => 120,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        // 邮件错误报警
        'errormail' => [
            'driver'    => 'monolog',
            'handler'   => SwiftMailerHandler::class,
            'formatter' => 'default',
            'with'    => [
                'level'   => Logger::ERROR,
                'subject' => env('APP_NAME') . ' Error Logs',
                'to'      => explode(',', env('LOG_MAIL_TO')),
                'cd_secs' => 60,
            ],
        ],

        // 邮件日志记录
        'email' => [
            'driver'    => 'monolog',
            'handler'   => SwiftMailerHandler::class,
            'formatter' => 'default',
            'with'    => [
                'level'   => Logger::DEBUG,
                'subject' => env('APP_NAME') . ' Info Logs',
                'to'      => explode(',', env('LOG_MAIL_TO')),
            ],
        ],

        // 落地到 Redis
        'redis' => [
            'driver'  => 'monolog',
            'handler' => RedisHandler::class,
            'with'    => [
                'level'    => Logger::DEBUG,
                'key'      => 'log_default',
                'cap_size' => 1000,
            ],
        ],

        // 队列执行失败
        'queue_failed' => [
            'driver'    => 'monolog',
            'handler'   => SwiftMailerHandler::class,
            'formatter' => 'default',
            'with'    => [
                'level'   => Logger::ERROR,
                'subject' => env('APP_NAME') . ' 队列任务失败',
                'to'      => explode(',', env('LOG_MAIL_TO')),
                'cd_secs' => 60,
            ],
        ],

        // 队列执行成功
        'queue_succeed' => [
            'driver'  => 'monolog',
            'handler' => DatabaseHandler::class,
            'with'    => [
                'level' => Logger::DEBUG,
                'table' => 'log_queue_succeed',
            ],
        ],

        // 运维预警
        'alarm_hourly' => [
            'driver'    => 'monolog',
            'handler'   => SwiftMailerHandler::class,
            'formatter' => 'default',
            'with'    => [
                'level'   => Logger::DEBUG,
                'subject' => env('APP_NAME') . ' 运维预警',
                'to'      => explode(',', env('LOG_MAIL_TO')),
                'cd_secs' => 3600,
            ],
        ],

        // 运维预警
        'alarm_daily' => [
            'driver'    => 'monolog',
            'handler'   => SwiftMailerHandler::class,
            'formatter' => 'default',
            'with'    => [
                'level'   => Logger::DEBUG,
                'subject' => env('APP_NAME') . ' 运维预警',
                'to'      => explode(',', env('LOG_MAIL_TO')),
                'cd_secs' => 43200,
            ],
        ],

        // 企业微信群机器人
        'work_wechat_robot' => [
            'driver'  => 'monolog',
            'handler' => WorkWechatGroupRobotHandler::class,
            'with'    => [
                'level'   => Logger::DEBUG,
                'sendKey' => env('LOG_WORK_WECHAT_ROBOT'),
            ],
        ],

    ],

];

$LOG_DB_CHANNELS = [
    'api_request',
];

foreach ($LOG_DB_CHANNELS as $channel) {
    $return['channels'][$channel] = [
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
