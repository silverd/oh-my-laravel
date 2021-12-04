<?php

namespace Silverd\OhMyLaravel\Helpers;

class ArrayHelper
{
    /**
     * 二维数组排序（可按多字段排序）
     *
     * @param array &$array
     * @param array $sortFields
     *                  $field1 => SORT_DESC,
     *                  $field2 => SORT_ASC,
     *                  ....
     * @return array
     */
    public static function multiSort(array &$array, array $sortFields)
    {
        if (! $array) {
            return $array;
        }

        $indexes = [];

        // 准备索引
        foreach ($array as $key => $value) {
            foreach ($sortFields as $sortField => $order) {
                $indexes[$sortField][$key] = $value[$sortField] ?? null;
            }
        }

        // 组合参数
        $args = [];

        foreach ($sortFields as $sortField => $order) {
            $args[] = $indexes[$sortField];
            $args[] = $order;
        }

        // 把 $array 作为最后一个参数，以通用键排序
        $args[] = &$array;

        call_user_func_array('array_multisort', $args);

        return $array;
    }

    /**
     * 将键值对数组转为指定格式字符串
     * 数组格式 [1 => 2, 3 => 4] 转换为 1:2;3:4
     *
     * @param array $array
     * @param string $split
     * @param bool $reverseKv 反转键值位置
     * @return string
     */
    public static function xEncode(array $array, string $split = ';', $reverseKv = false)
    {
        if (! $array) {
            return false;
        }

        $string = $comma = '';

        foreach ($array as $key => $value) {
            if ($reverseKv) {
                $string .= $comma . $value . ':' . $key;
            } else {
                $string .= $comma . $key . ':' . $value;
            }
            $comma = $split;
        }

        return $string;
    }

    /**
     * 将指定格式的字符串转为键值对数组
     * 字符串格式：1:2;3:4 转换为 [1 => 2, 3 => 4]
     *
     * @param string $arrStr
     * @param string $split
     * @param bool $reverseKv 反转键值位置
     * @return array
     */
    public static function xDecode(string $arrStr, string $split = ';', $reverseKv = false)
    {
        if (! $arrStr || ! $arrStr = explode($split, $arrStr)) {
            return [];
        }

        $return = [];

        foreach ($arrStr as $str) {
            if ($str) {
                [$key, $value] = explode(':', $str);
                if ($reverseKv) {
                    $return[$value] = $key;
                } else {
                    $return[$key] = $value;
                }
            }
        }

        return $return;
    }

    // 键值数组转换为同级多列数组
    public static function convertToList(
        array $array,
        string $keyColumn = 'code',
        string $valColumn = 'name',
        bool $recurse = true
    )
    {
        $list = [];

        foreach ($array as $index => $value) {
            if ($recurse && is_array($value)) {
                $list[$index] = self::convertToList($value, $keyColumn, $valColumn);
            }
            else {
                $list[] = [
                    $keyColumn => $index,
                    $valColumn => $value,
                ];
            }
        }

        return $list;
    }

    // 遍历筛选列表的指定列
    public static function onlys(array $array, array $keys)
    {
        return array_map(function ($row) use ($keys) {
            return \Arr::only($row, $keys);
        }, $array);
    }

    // 支持模糊排除某些下标
    public static function except(array $array, $keys)
    {
        foreach ($array as $key => $value) {
            if (\Str::is($keys, $key)) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * 随机取数组中的若干元素
     *
     * @param array $array
     * @param int $rndNum 取几个
     * @return mixed
     */
    public static function rand(array $array, int $rndNum = 1)
    {
        if (! $array || ! is_array($array)) {
            return false;
        }

        if (count($array) < $rndNum) {
            return $array;
        }

        // 随机一个
        if ($rndNum == 1) {
            return $array[array_rand($array)];
        }

        // 随机多个
        $result = [];

        $randKeys = array_rand($array, $rndNum);
        foreach ($randKeys as $randKey) {
            $result[] = $array[$randKey];
        }

        return $result;
    }

    /**
     * 将数组 value 中的某一个字段的值，赋给该数组的 key
     *
     * @param array $array
     * @param string $field
     * @return array
     */
    public static function indexField(array $array, string $field)
    {
        $result = [];

        foreach ($array as $key => $value) {
            $result[$value[$field]] = $value;
        }

        return $result;
    }

    /**
     * 数组求和、或数组某字段求和
     *
     * @param array $array
     * @param string $field
     * @return array
     */
    public static function sum(array $array, ?string $field = null)
    {
        if (! $array) {
            return 0;
        }

        if (null === $field) {
            return array_sum($array);
        }

        $sum = 0;

        foreach ($array as $value) {
            $sum += $value[$field];
        }

        return $sum;
    }
}
