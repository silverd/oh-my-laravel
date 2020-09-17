<?php

namespace Silverd\OhMyLaravel\Helpers;

use Carbon\Carbon;

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
    public static function createSn(string $namespace = 'default', string $prefix = '', int $length = 8)
    {
        $insertId = \Redis::incr('FlowSn:' . ucfirst($namespace));

        $suffix = self::getSnSuffix();

        return $prefix . date('ymdHi') . str_pad(substr($insertId, -$length), $length, 0, STR_PAD_LEFT) . $suffix;
    }

    // 填充日期
    public static function fillDate(
        string $startDate,
        string $endDate,
        array $data,
        callable $callback,
        bool $isAsc = false
    )
    {
        $completed = [];

        $startDate = Carbon::parse($startDate);
        $endDate   = Carbon::parse($endDate);

        // 升序
        if ($isAsc) {
            while ($startDate->lte($endDate)) {
                $today = $startDate->toDateString();
                $completed[] = $callback($today, $data[$today] ?? null);
                $startDate->addDay();
            }
        }
        // 倒序
        else {
            while ($endDate->gte($startDate)) {
                $today = $endDate->toDateString();
                $completed[] = $callback($today, $data[$today] ?? null);
                $endDate->subDay();
            }
        }

        return $completed;
    }

    // 填充小时
    public static function fillHour(
        array $data,
        callable $callback,
        bool $isAsc = false
    )
    {
        $completed = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $hourKey = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $result  = $callback($hourKey, $data[$hourKey] ?? $data[intval($hourKey)] ?? null);
            if ($result === false) {
                break;
            }
            $completed[] = $result;
        }

        if (! $isAsc) {
            $completed = array_reverse($completed);
        }

        return $completed;
    }

    // 填充月份
    public static function fillMonth(
        string $startMonth,
        string $endMonth,
        array $data,
        callable $callback,
        bool $isAsc = false
    )
    {
        $completed  = [];

        $startMonth = Carbon::parse($startMonth)->startOfMonth();
        $endMonth   = Carbon::parse($endMonth);

        // 升序
        if ($isAsc) {
            while ($startMonth->lte($endMonth)) {
                $month = $endMonth->format('Y-m');
                $completed[] = $callback($month, $data[$month] ?? null);
                $startMonth->addMonth();
            }
        }
        // 倒序
        else {
            while ($endMonth->gte($startMonth)) {
                $month = $endMonth->format('Y-m');
                $completed[] = $callback($month, $data[$month] ?? null);
                $endMonth->subMonth();
            }
        }

        return $completed;
    }

    // 填充日期和小时
    public static function fillDateWithHour(
        string $startDate,
        string $endDate,
        array $data,
        callable $callback,
        bool $isAsc = false
    )
    {
        $fillHourCb = function (string $today, ?array $todayArr) use ($isAsc, $callback) {

            $todayArr = array_column($todayArr ?? [], null, 'hour');

            return self::fillHour($todayArr, function (string $hour, ?array $hourArr) use ($today, $callback) {

                if ($today == date('Y-m-d') && $hour > date('H')) {
                    // 不显示未来的时间
                    return false;
                }

                $today .= ' ' . $hour . ':00';

                return $callback($today, $hourArr);

            }, $isAsc);
        };

        return self::fillDate($startDate, $endDate, $data, $fillHourCb, $isAsc);
    }

    // 填充周
    public static function fillWeek(
        string $startDate,
        string $endDate,
        array $data,
        callable $callback,
        bool $isAsc = false
    )
    {
        $completed = [];

        $startDate = Carbon::parse($startDate)->startOfWeek();
        $endDate   = Carbon::parse($endDate)->startOfWeek();

        // 升序
        if ($isAsc) {
            while ($startDate->lte($endDate)) {
                $today = $startDate->toDateString();
                $completed[] = $callback($today, $data[$today] ?? null);
                $startDate->addWeek();
            }
        }
        // 倒序
        else {
            while ($endDate->gte($startDate)) {
                $today = $endDate->toDateString();
                $completed[] = $callback($today, $data[$today] ?? null);
                $endDate->subWeek();
            }
        }

        return $completed;
    }
}
