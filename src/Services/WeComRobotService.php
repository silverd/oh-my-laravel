<?php

namespace Silverd\OhMyLaravel\Services;

/**
 * 企业微信-群机器人
 *
 * @author JiangJian <jian.jiang@goldentec.com>
 *
 * @see https://work.weixin.qq.com/api/doc/90000/90136/91770
 */

class WeComRobotService extends AbstractService
{
    const API_URL = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send';

    public function sendText(string $content, array $atSbs = [], array $atMobiles = [])
    {
        $params = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content,
                'mentioned_list' => $atSbs,
                'mentioned_mobile_list' => $atMobiles,
            ],
        ];

        return $this->request($params);
    }

    public function sendMarkdown(string $content, array $atSbs = [])
    {
        if ($atSbs) {
            $content .= str_repeat(PHP_EOL, 3) . implode(PHP_EOL, array_map(function ($name) {
                return '<@' . $name . '>';
            }, $atSbs));
        }

        $params = [
            'msgtype' => 'markdown',
            'markdown' => [
                'content' => $content,
            ],
        ];

        return $this->request($params);
    }

    public function sendImage(string $imgStream)
    {
        $params = [
            'msgtype' => 'image',
            'image' => [
                'base64' => base64_encode($imgStream),
                'md5'    => md5($imgStream),
            ],
        ];

        return $this->request($params);
    }

    /**
     * 图文类型
     *
     * @param  array $articles
     *         title
     *         description
     *         url
     *         picurl
     * @return array
     */
    public function sendNews(array $articles)
    {
        $params = [
            'msgtype' => 'news',
            'news' => [
                'articles' => $articles,
            ],
        ];

        return $this->request($params);
    }

    private function request(array $params)
    {
        $url = self::API_URL . '?key=' . $this->config['group_key'];

        $response = \Http::post($url, $params);

        $result = json_decode($response->body(), true);

        return $result;
    }
}
