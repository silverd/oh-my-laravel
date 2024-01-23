<?php

namespace Silverd\OhMyLaravel\Extensions\Logger;

use Monolog\Handler\HandlerInterface;
use Monolog\Processor\WebProcessor;
use Monolog\LogRecord;
use Illuminate\Log\LogManager as BaseLogManager;

class LogManager extends BaseLogManager
{
    protected function prepareHandler(HandlerInterface $handler, array $config = [])
    {
        $handler = parent::prepareHandler($handler, $config);

        if (method_exists($handler, 'pushProcessor')) {

            // 额外记录环境变量
            $handler->pushProcessor(new WebProcessor);

            // 额外记录请求数据
            $handler->pushProcessor(function (LogRecord $record) {
                $record['extra']['req_sn']  = $GLOBALS['_REQUEST_SN'] ?? '';
                $record['extra']['headers'] = \Request::header();
                $record['extra']['gets']    = \Request::all();
                $record['extra']['posts']   = \Request::post();
                $record['extra']['cookies'] = \Request::cookie();
                return $record;
            });
        }

        return $handler;
    }
}
