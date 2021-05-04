<?php

/**
 * 文件保存至 COS 异步处理
 * 目的：提高接口响应速度，不会因为 COS 的异常影响主流程
 *
 * @author 527
 */

namespace App\Jobs;

use Illuminate\Database\Eloquent\Model;

class StorageByUrlJob extends AbstractJob
{
    protected $model;
    protected $field;
    protected $params;

    public function __construct(Model $model, string $field, array $params)
    {
        $this->model  = $model;
        $this->field  = $field;
        $this->params = $params;
    }

    public function handle()
    {
        $newUrl = storageByUrl(...$this->params);

        $this->model->update([$this->field => $newUrl]);
    }
}
