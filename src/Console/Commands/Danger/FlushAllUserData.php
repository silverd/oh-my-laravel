<?php

/**
 * 清除所有用户数据
 *
 * @author JiangJian <silverd@sohu.com>
 */

namespace Silverd\OhMyLaravel\Console\Commands\Danger;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class FlushAllUserData extends Command
{
    protected $signature = 'flush:all-user-data';

    protected $description = '危险操作：清除所有用户数据';

    public function handle()
    {
        if (\App::environment('production')) {
            return $this->error('生产环境禁止执行本操作');
        }

        if (! $this->confirm($this->description . '吗？该操作不可逆！')) {
            return false;
        }

        // 清除数据表
        $this->flushDb();

        // 清除 Redis
        $this->flushRedis();

        // 清除应用缓存
        \Cache::store('file')->flush();
        \Cache::store('redis')->flush();
        \Cache::store('forever')->flush();

        $this->afterHandle();

        $this->info('所有用户数据已清理完毕！');
    }

    protected function afterHandle()
    {
        // 由子类继承
    }

    protected function flushRedis()
    {
        $dbs = config('database.redis');

        foreach ($dbs as $db => $config) {
            if (isset($config['host'])) {
                Redis::connection($db)->flushDb();
            }
        }
    }

    protected function flushDb()
    {
        $config = config('oh-my-laravel.flush');

        // 受保护的表
        $skipTables = $config['skip_tables'];

        // 强行清理的表
        $forceTables = $config['force_tables'];

        // 可以删除的表
        $removeTables = $config['remove_tables'];

        $tables = \DB::select('SHOW TABLES');

        foreach ($tables as $table) {

            $tableName = current($table);

            // 受保护的表
            $skipped = \Arr::first($skipTables, function ($value) use ($tableName) {
                return \Str::is($value, $tableName);
            });

            if ($skipped && ! in_array($tableName, $forceTables)) {
                $this->info("skipped: {$tableName}");
                continue;
            }

            // 待删除
            $removable = \Arr::first($removeTables, function ($value) use ($tableName) {
                return \Str::is($value, $tableName);
            });

            if ($removable) {
                $this->removeTbl($tableName);
            }
            else {
                $this->truncateTbl($tableName);
            }
        }
    }

    protected function truncateTbl($tableName)
    {
        try {
            \DB::table($tableName)->truncate();
            $this->line("truncated: {$tableName}");
        }
        catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }

    protected function removeTbl($tableName)
    {
        try {
            \Schema::dropIfExists($tableName);
            $this->line("removed: {$tableName}");
        }
        catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
