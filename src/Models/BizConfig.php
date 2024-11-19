<?php

namespace Silverd\OhMyLaravel\Models;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BizConfig extends AbstractModel
{
    protected $table = 'config_biz';

    protected $casts = [
        'scopes' => 'array',
    ];

    const
        VALUE_TYPE_INPUT    = 1,
        VALUE_TYPE_TEXTAREA = 2,
        VALUE_TYPE_EDITOR   = 3,
        VALUE_TYPE_PASSWORD = 4;

    const VALUE_TYPES = [
        self::VALUE_TYPE_INPUT    => '输入框',
        self::VALUE_TYPE_TEXTAREA => '文本域',
        self::VALUE_TYPE_EDITOR   => '富文本',
        self::VALUE_TYPE_PASSWORD => '密码框',
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

    protected function value(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                return $this->value_type == self::VALUE_TYPE_PASSWORD ? Crypt::decrypt($value) : $value;
            },
            set: function ($value) {
                return $this->value_type == self::VALUE_TYPE_PASSWORD ? Crypt::encrypt($value) : $value;
            }
        );
    }

    public static function fetchAll()
    {
        $config = config('oh-my-laravel');

        $on = $config['biz_config'] ?? 1;

        if (! $on) {
            return [];
        }

        $getter = function () {
            return static::get()->pluck('value', 'key')->toArray();
        };

        // 无需缓存
        if ($on === 2) {
            return $getter();
        }

        return \Cache::rememberForever('bizConfig', $getter);
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
