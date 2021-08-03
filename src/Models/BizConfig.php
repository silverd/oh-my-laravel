<?php

namespace Silverd\OhMyLaravel\Models;

class BizConfig extends AbstractModel
{
    protected $table = 'config_biz';

    protected static function booted()
    {
        $callback = function () {
            static::clearCache();
        };

        // 有更新则清除缓存
        static::saved($callback);
        static::deleted($callback);
    }

    private static function fetchAll()
    {
        if (! config('oh-my-laravel.biz_config')) {
            return [];
        }

        return \Cache::rememberForever('bizConfig', function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    private static function clearCache()
    {
        return \Cache::forget('bizConfig');
    }

    public static function initConfig()
    {
        foreach (static::fetchAll() as $key => $value) {
            config(['biz.' . $key => $value]);
        }
    }
}
