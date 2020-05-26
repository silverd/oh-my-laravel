<?php

return [

    // 需要记录「任务执行成功日志」的队列类名
    'log_succeed_jobs' => [
        // \App\Jobs\EvaluateOrderJob::class,
    ],

    // 清理数据配置
    'flush' => [

        'skip_tables' => [
            'admin_*',
            'migrations',
            'config_biz',
        ],

        'force_tables' => [
            'admin_operation_log',
        ],

        'remove_tables' => [
            'log_*',
        ],

        // 标明用户 id 字段非 uid 的表
        'x_table_uid_keys' => [
            'users' => ['id'],
        ],

    ],

];
