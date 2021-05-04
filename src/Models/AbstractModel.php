<?php

namespace Silverd\OhMyLaravel\Models;

use App\Jobs\StorageByUrlJob;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

abstract class AbstractModel extends Model
{
    protected $guarded = [];

    // 模型中文名
    protected $modelName = '';

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
        return static::STATUS_TEXTS[$this->status] ?? '-';
    }

    public function checkStatus($status, string $message = '')
    {
        $statuses = (array) $status;

        if (! in_array($this->status, $statuses)) {
            $name = $this->modelName ?: class_basename(get_called_class());
            throws($message ?: $name . ' 状态必须为' . implode('/', \Arr::only(static::STATUS_TEXTS, $statuses)));
        }

        return $this;
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

    // 异步将指定字段的 URL 资源转存到 COS
    public function asyncStorageByUrl(string $field, string $dirName = '', string $extension = '.jpg')
    {
        if (! $this->$field) {
            return false;
        }

        // 如果该 URL 本来就是 COS 上的资源，所以无需转存
        if (starts_with($this->$field, \Storage::getAdapter()->getPathPrefix())) {
            return false;
        }

        StorageByUrlJob::dispatch($this, $field, [$dirName ?: $field, $this->$field, $extension]);
    }
}
