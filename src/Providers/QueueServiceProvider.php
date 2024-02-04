<?php

namespace Silverd\OhMyLaravel\Providers;

use Queue as QueueManager;
use Illuminate\Queue\Queue as QueueObject;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Silverd\OhMyLaravel\Models\BizConfig;

class QueueServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 每个任务入列前
        QueueObject::createPayloadUsing(function ($connection, $queue, $payload) {
            return [
                'req_sn' => $GLOBALS['_REQUEST_SN'] ?? '',
            ];
        });

        // 每个任务弹出前
        QueueManager::looping(function () {
            // 读取最新的业务级配置缓存
            // 保证在常驻进程中也可以获取已发生变化的配置参数
            BizConfig::initConfig();
        });

        // 每个任务弹出后、执行前
        QueueManager::before(function (JobProcessing $event) {
            // 重置请求流水号
            $GLOBALS['_REQUEST_SN'] = $event->job->payload()['req_sn'] ?? '';
        });

        // 每个任务执行成功后
        QueueManager::after(function (JobProcessed $event) {
            $payload = $event->job->payload();
            $jobClass = $payload['data']['commandName'];
            if (in_array($jobClass, config('oh-my-laravel.log_succeed_jobs', []))) {
                \Log::channel('queue_succeed')->info('队列任务成功', [
                    'job_name'    => $payload['displayName'],
                    'job_payload' => $payload,
                    'connection'  => $event->connectionName,
                ]);
            }
        });

        // 每个任务执行失败后
        QueueManager::failing(function (JobFailed $event) {
            $payload = $event->job->payload();
            \Log::channel('queue_failed')->error('队列任务失败', [
                'job_name'    => $payload['displayName'],
                'job_payload' => $payload,
                'connection'  => $event->connectionName,
                'exception'   => getFullException($event->exception),
            ]);
        });
    }
}
