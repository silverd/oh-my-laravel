<?php

namespace Silverd\OhMyLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use Silverd\OhMyLaravel\Models\BizConfig;

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
        BizConfig::initConfig();

        // 一些表单验证规则
        $this->initValidateRules();
    }

    // 一些表单验证规则
    protected function initValidateRules()
    {
        \Validator::extend('zh_mobile', function ($attribute, $value) {
            return preg_match('/^(\+?0?86\-?)?1[3-9]{1}\d{9}$/', $value);
        }, '无效的手机号格式');
    }
}
