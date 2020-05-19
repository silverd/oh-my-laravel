<?php

namespace Silverd\OhMyLaravel\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

abstract class AbstractModel extends Model
{
    /**
     * all attributes mass assignable
     *
     * @var array
     */
    protected $guarded = [];

    const
        STATUS_ENABLED  = 1,  // 启用
        STATUS_DISABLED = 0;  // 禁用

    const STATUS_TEXTS = [
        self::STATUS_ENABLED  => '启用',
        self::STATUS_DISABLED => '禁用',
    ];

    public function scopeEnabled($query)
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    public function getStatusTextAttribute()
    {
        if (! isset(static::STATUS_TEXTS[$this->status])) {
            return '-';
        }

        return static::STATUS_TEXTS[$this->status];
    }

    // 批量自增多个字段，同时也可更新字段
    public function increments(array $attributes, array $updated = [])
    {
        $originals = [];

        foreach ($attributes as $column => $amount) {
            $originals[$column] = $this->{$column};
            $updated[$column] = \DB::raw("`{$column}` + {$amount}");
        }

        if (! $this->fill($updated)->save()) {
            return false;
        }

        // 同步模型的字段值（否则为 Expression 类型）
        foreach ($attributes as $column => $amount) {
            $this->{$column} = $originals[$column] + $amount;
            $this->syncOriginalAttribute($column);
        }

        return true;
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
