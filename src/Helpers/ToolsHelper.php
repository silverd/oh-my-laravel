<?php

namespace Silverd\OhMyLaravel\Helpers;

class ToolsHelper
{
    public static function getSnSuffix()
    {
        static $suffixes = [
            'dev'        => 1,
            'qa'         => 2,
            'production' => 0,
        ];

        $env = config('app.env');

        $suffix = $suffixes[$env] ?? 9;

        return $suffix;
    }

    /**
     * 生成订单流水号（18位数字）
     * 最大可以支持1分钟1亿订单号不重复
     *
     * @return string $orderSn
     */
    public static function createSn($namespace = 'default', $prefix = '', $length = 8)
    {
        $insertId = \Redis::incr('FlowSn:' . ucfirst($namespace));

        $suffix = self::getSnSuffix();

        return $prefix . date('ymdHi') . str_pad(substr($insertId, -$length), $length, 0, STR_PAD_LEFT) . $suffix;
    }
}
