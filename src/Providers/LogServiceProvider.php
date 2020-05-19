<?php

namespace Silverd\OhMyLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Handler\RedisHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Formatter\HtmlFormatter;
use Silverd\OhMyLaravel\Extensions\Logger\LogManager;
use Silverd\OhMyLaravel\Extensions\Logger\Handler\DeduplicationHandler;

class LogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 覆盖原有的日志工厂
        $this->app->singleton('log', function ($app) {
            return new LogManager($app);
        });

        // 邮件日志处理器
        $this->app->bind(SwiftMailerHandler::class, function ($app, array $with) {

            $config = $app['config']['mail'];

            $message = (new \Swift_Message($with['subject']))
                ->setFrom($config['from']['address'], $config['from']['name'])
                ->setTo($with['to'])
                ->setContentType('text/html');

            $handler = new SwiftMailerHandler($app->make('swift.mailer'), $message, $with['level']);

            // 以 HTML 格式输出
            $handler->setFormatter(new HtmlFormatter);

            // 重复消息冷却去重（装饰模式）
            if (isset($with['cd_secs']) && $with['cd_secs'] > 0) {
                return new DeduplicationHandler(
                    $handler,
                    $app['cache']->store('redis'),
                    $with['level'],
                    $with['cd_secs']
                );
            }

            return $handler;
        });

        // Redis 日志处理器
        $this->app->bind(RedisHandler::class, function ($app, array $with) {

            $redis = $app['redis']->connection('logging');

            return new RedisHandler(
                $redis->client(),
                $with['key'],
                $with['level'],
                true,
                $with['cap_size']
            );
        });
    }
}
