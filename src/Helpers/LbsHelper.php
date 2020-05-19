<?php

namespace Silverd\OhMyLaravel\Helpers;

class LbsHelper
{
    /**
     * 计算两个坐标之间的距离（米）
     *
     * @param array $fromPoint 起点 [经度, 纬度]
     * @param array $destPoint 终点 [经度, 纬度]
     * @return int 距离（米）
     */
    public static function calcDistance(array $fromPoint, array $destPoint)
    {
        // 地球半径
        $fEARTH_RADIUS = 6378137;

        // 角度换算成弧度
        $fRadLng1 = deg2rad($fromPoint[0]);
        $fRadLng2 = deg2rad($destPoint[0]);
        $fRadLat1 = deg2rad($fromPoint[1]);
        $fRadLat2 = deg2rad($destPoint[1]);

        // 计算经纬度的差值
        $fD1 = abs($fRadLat1 - $fRadLat2);
        $fD2 = abs($fRadLng1 - $fRadLng2);

        // 距离计算
        $fP = pow(sin($fD1/2), 2) + cos($fRadLat1) * cos($fRadLat2) * pow(sin($fD2/2), 2);

        return intval($fEARTH_RADIUS * 2 * asin(sqrt($fP)) + 0.5);
    }
}
