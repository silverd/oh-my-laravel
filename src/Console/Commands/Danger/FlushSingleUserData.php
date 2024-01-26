<?php

/**
 * 清除单个用户数据
 *
 * @author JiangJian <jian.jiang@wetax.com.cn>
 */

namespace Silverd\OhMyLaravel\Console\Commands\Danger;

use App\Models\User;
use Illuminate\Console\Command;

class FlushSingleUserData extends Command
{
    protected $signature = 'flush:single-user-data
        {--uid= : UID}
        {--mobile= : 手机号}';

    protected $description = '危险操作：清除单个用户数据';

    public function handle()
    {
        if (\App::environment('production')) {
            $this->error('生产环境禁止执行本操作');
            return false;
        }

        if ($mobile = $this->option('mobile')) {
            $user = $this->getUserByMobile($mobile);
        }
        elseif ($uid = $this->option('uid')) {
            $user = $this->getUserByUid($uid);
        }
        else {
            $this->error('UID 和手机号两者必填其一');
            return false;
        }

        if (! $user) {
            $this->error('找不到目标用户');
            return false;
        }

        if (! $this->confirm('确定要删除用户 (UID=' . $user->id . '|手机号=' . $user->mobile . ') 吗？该操作不可逆！')) {
            return false;
        }

        $this->flushDb($user->id);

        $this->info('单个用户数据已清理完毕！');
    }

    protected function getUserByUid(int $uid)
    {
        return User::find($uid);
    }

    protected function getUserByMobile(string $mobile)
    {
        return User::where('mobile', $mobile)->first();
    }

    protected function flushDb(int $uid)
    {
        $tables = \DB::select('SHOW TABLES');

        $xTableUidKeys = config('oh-my-laravel.flush.x_table_uid_keys');

        foreach ($tables as $table) {
            $tableName = current($table);
            $this->removeFromTbl($tableName, 'uid', $uid);
            if (isset($xTableUidKeys[$tableName])) {
                foreach ($xTableUidKeys[$tableName] as $pk) {
                    $this->removeFromTbl($tableName, $pk, $uid);
                }
            }
        }
    }

    protected function removeFromTbl(string $tableName, string $pk, int $uid)
    {
        if (! \Schema::hasColumn($tableName, $pk)) {
            return null;
        }
        try {
            \DB::table($tableName)->where($pk, $uid)->delete();
            $this->line("removed from: {$tableName} [{$pk}]");
        }
        catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
