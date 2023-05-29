<?php

namespace Silverd\OhMyLaravel\Models;

class BizConfig extends AbstractModel
{
    protected $table = 'config_biz';

    protected $casts = [
        'scopes' => 'array',
    ];

    const
        VALUE_TYPE_INPUT    = 1,
        VALUE_TYPE_TEXTAREA = 2,
        VALUE_TYPE_EDITOR   = 3;

    const VALUE_TYPES = [
        self::VALUE_TYPE_INPUT    => '输入框',
        self::VALUE_TYPE_TEXTAREA => '文本域',
        self::VALUE_TYPE_EDITOR   => '富文本',
    ];

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
        $config = config('oh-my-laravel');

        $on = $config['biz_config'] ?? true;

        if (! $on) {
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

    public function getScopesAttribute($value)
    {
        return is_string($value) ? $this->fromJson($value) : (array) $value;
    }

    public function setScopesAttribute($value)
    {
        $this->attributes['scopes'] = $this->asJson($value ? explode(',', $value) : []);
    }
}
