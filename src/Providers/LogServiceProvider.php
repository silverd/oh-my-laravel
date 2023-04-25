<?php

namespace Silverd\OhMyLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Handler\RedisHandler;
use Monolog\Handler\MongoDBHandler;
use Monolog\Handler\SymfonyMailerHandler;
use Monolog\Formatter\HtmlFormatter;
use Silverd\OhMyLaravel\Extensions\Logger\LogManager;
use Silverd\OhMyLaravel\Extensions\Logger\Handler\DeduplicationHandler;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class LogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 覆盖原有的日志工厂
        $this->app->singleton('log', function ($app) {
            return new LogManager($app);
        });

        // 邮件日志处理器
        $this->app->bind(SymfonyMailerHandler::class, function ($app, array $with) {

            $config = $app['config']['mail'];
            $smtp = $config['mailers']['smtp'];

            $transport = Transport::fromDsn(
                $smtp['transport'] . '://' .
                rawurlencode($smtp['username']) . ':' . rawurlencode($smtp['password']) . '@' .
                $smtp['host'] . ':' . $smtp['port']
            );

            $mailer = new Mailer($transport);

            $email = (new Email())
                ->from(new Address($config['from']['address'], $config['from']['name']))
                ->to(...$with['to'])
                ->subject($with['subject']);

            $handler = new SymfonyMailerHandler($mailer, $email, $with['level']);

            // 以 HTML 格式输出
            $handler->setFormatter(new HtmlFormatter);

            // 重复消息冷却去重（装饰模式）
            if (isset($with['cd_secs']) || isset($with['buffer_limit'])) {
                return new DeduplicationHandler(
                    $handler,
                    $app['cache']->store('redis'),
                    $with['level'],
                    $with['cd_secs'] ?? 0,
                    $with['buffer_limit'] ?? 1,
                    $with['flush_on_overflow'] ?? true,
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

        // MongoDB 日志处理器
        $this->app->bind(MongoDBHandler::class, function ($app, array $with) {

            $mongodb = \DB::connection($with['connection'] ?? 'mongodb')->getMongoClient();

            return new MongoDBHandler(
                $mongodb,
                $with['database'],
                $with['collection'],
                $with['level']
            );

        });
    }
}
