<?php

// 清除X天前的日志表

namespace Silverd\OhMyLaravel\Console\Commands\Utils;

use Illuminate\Console\Command;
use Carbon\Exceptions\InvalidFormatException;

class FlushDbLogs extends Command
{
    protected $signature = 'flush:db-logs {--conn=} {--days=}';

    protected $description = '删除7天前的的日志表';

    public function handle()
    {
        $conn = $this->option('conn') ?: 'mysql_log';
        $maxDays = $this->option('days') ?: 7;

        $tables = \DB::connection($conn)->select('SHOW TABLES');

        foreach ($tables as $table) {

            $tableName = current((array) $table);

            if (! \Str::is('log_*', $tableName)) {
                continue;
            }

            $date = '20' . substr($tableName, -6);

            try {
                // 七天内日志保留
                if (now()->diffInDays($date) <= $maxDays) {
                    continue;
                }
            }
            catch (InvalidFormatException $e) {
                continue;
            }

            \Schema::connection($conn)->drop($tableName);

            $this->info('日志表 ' . $tableName . ' 删除完成');
        }
    }
}
