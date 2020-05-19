<?php

/**
 * 清除单个用户数据
 *
 * @author JiangJian <jian.jiang@wetax.com.cn>
 */

namespace App\Console\Commands\Danger;

use Silverd\OhMyLaravel\Console\Commands\Danger\FlushSingleUserData as BaseFlushSingleUserData;

class FlushSingleUserData extends BaseFlushSingleUserData
{
    protected function getUserByUid(int $uid)
    {
        return parent::getUserByUid($uid);
    }

    protected function getUserByMobile(string $mobile)
    {
        return parent::getUserByMobile($mobile);
    }
}
