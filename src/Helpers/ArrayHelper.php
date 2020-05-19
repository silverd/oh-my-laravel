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

        // 准备索引
        foreach ($array as $key => $value) {
            foreach ($sortFields as $sortField => $order) {
                ${$sortField}[$key] = isset($value[$sortField]) ? $value[$sortField] : null;
            }
        }

        // 组合参数
        $args = [];
        foreach ($sortFields as $sortField => $order) {
            $args[] = ${$sortField};
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
                list($key, $value) = explode(':', $str);
                if ($reverseKv) {
                    $return[$value] = $key;
                } else {
                    $return[$key] = $value;
                }
            }
        }

        return $return;
    }
}
