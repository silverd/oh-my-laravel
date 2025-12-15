<?php

namespace Silverd\OhMyLaravel\Extensions\Logger\Handler;

use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Handler\AbstractProcessingHandler;
use Illuminate\Support\Facades\Http;

/**
 * 企业微信-群机器人
 *
 * @author JiangJian <silverd@sohu.com>
 *
 * @see https://work.weixin.qq.com/api/doc/90000/90136/91770
 */

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class WorkWechatGroupRobotHandler extends AbstractProcessingHandler
{
    protected $title;
    protected $sendKey;
    protected $cdSecs;

    public function __construct(
        string $title,
        string $sendKey,
        int $cdSecs = 10,
        $level = Level::Error,
        $bubble = true
    )
    {
        parent::__construct($level, $bubble);

        $this->title   = $title;
        $this->sendKey = $sendKey;
        $this->cdSecs  = $cdSecs;
    }

    protected function write(LogRecord $record): void
    {
        $cacheKey = 'LogDeduplication:' . sha1($record->message);

        if ($this->cdSecs > 0 && ! Cache::add($cacheKey, 1, now()->addSeconds($this->cdSecs))) {
            return;
        }

        $url = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=' . $this->sendKey;

        Http::post($url, [
            'msgtype'  => 'markdown',
            'markdown' => [
                'content' => $this->title . PHP_EOL
                    . '><font color=\"warning\">' . Str::limit($record->formatted, 800) . '</font>',
            ],
        ]);
    }
}
