<?php

namespace Silverd\OhMyLaravel\Extensions\Logger\Handler;

use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * 企业微信-群机器人
 *
 * @author JiangJian <silverd@sohu.com>
 *
 * @see https://work.weixin.qq.com/api/doc/90000/90136/91770
 */

class WorkWechatGroupRobotHandler extends AbstractProcessingHandler
{
    protected $title;
    protected $sendKey;

    public function __construct(string $title, string $sendKey, $level = Level::Error, $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->title = $title;
        $this->sendKey = $sendKey;
    }

    protected function write(LogRecord $record): void
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=' . $this->sendKey;

        $title = config('app.name') . ' - ' . $this->title;

        $response = \Http::post($url, [
            'msgtype' => 'text',
            'text' => [
                'content' => $title . ' / ' . ($record->extra['req_sn'] ?? '') . ' / ' . $record->message,
            ],
        ]);
    }
}
