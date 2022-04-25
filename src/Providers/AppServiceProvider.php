<?php

namespace Silverd\OhMyLaravel\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Silverd\OhMyLaravel\Models\BizConfig;
use Silverd\OhMyLaravel\Helpers\IDCardHelper;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 请求流水号
        $GLOBALS['_REQUEST_SN'] = date('Ymd') . '-' . \Str::orderedUuid();

        // 响应宏
        \Response::macro('output', function (string $message, $code = 0, $data = null) {

            // 为保持输出的空 map/list 结构一致
            // 这里统一把所有空 array 转换为 null
            if (is_array($data)) {
                $data = setEmptyArrayToNull($data);
            }

            // 总耗时
            $elapsed = round(microtime(true) - LARAVEL_START, 3);

            return toJson([
                'code'    => $code,
                'message' => $message,
                'data'    => $data,
                'elapsed' => $elapsed,
                'req_sn'  => $GLOBALS['_REQUEST_SN'],
            ]);
        });

        // 默认的 MySQL 字符串字段长度
        \Schema::defaultStringLength(191);

        // 不对已存在的 HTML 实体进行编码
        \Blade::withoutDoubleEncoding();

        // 加载全局业务配置
        $this->initBizConfig();

        // 一些表单验证规则
        $this->initValidateRules();

        // 集合的一些扩展方法
        $this->initCollectionMarco();
    }

    // 加载全局业务配置
    protected function initBizConfig()
    {
        try {
            BizConfig::initConfig();
        }
        catch (\Throwable $e) {
        }
    }

    // 一些表单验证规则
    protected function initValidateRules()
    {
        \Validator::extend('zh_mobile', function ($attribute, $value) {
            return preg_match('/^(\+?0?86\-?)?1[3-9]{1}\d{9}$/', $value);
        }, '无效的手机号格式');

        // 表单验证扩展：身份证
        \Validator::extend('zh_idcard', function ($attribute, $value) {
            return IDCardHelper::validate($value);
        }, '身份证格式不正确');
    }

    protected function initCollectionMarco()
    {
        Collection::macro('ungroup', function () {

            // create a new collection to use as the collection where the other collections are merged into
            $newCollection = Collection::make([]);

            // $this is the current collection ungroup() has been called on
            // binding $this is common in JS, but this was the first I had run across it in PHP
            $this->each(function ($item) use (&$newCollection) {
                // use merge to combine the collections
                $newCollection = $newCollection->merge($item);
            });

            return $newCollection;

        });

        Collection::macro('sortByMulti', function (array $keys) {

            $currentIndex = 0;

            $keys = array_map(function ($key, $sort) {
                return ['key' => $key, 'sort' => $sort];
            }, array_keys($keys), $keys);

            $sortBy = function (Collection $collection) use (&$currentIndex, $keys, &$sortBy) {

                if ($currentIndex >= count($keys)) {
                    return $collection;
                }

                $key = $keys[$currentIndex]['key'];
                $sort = $keys[$currentIndex]['sort'];
                $sortFunc = $sort === SORT_DESC ? 'sortByDesc' : 'sortBy';
                $currentIndex++;

                return $collection->$sortFunc($key)->groupBy($key)->map($sortBy)->ungroup();
            };

            return $sortBy($this);

        });
    }
}
