<?php

namespace Silverd\OhMyLaravel\Helpers;

class SSEHelper
{
    // 格式化 SSE 输出
    // @see https://www.ruanyifeng.com/blog/2017/05/server-sent_events.html
    public static function print(string $str, int $padding = 0, $lastEventId = null)
    {
        $data = \Str::startsWith($str, ['[DONE]', '[ERROR]']) ? $str : jsonEncode(['content' => $str]);

        $message = [
            'id'   => $lastEventId,
            // 为了填满 Nginx fastcgi_buffers 否则不会刷出
            'data' => str_pad($data, $padding),
        ];

        $output = '';

        foreach ($message as $key => $value) {
            $output .= $key . ': ' . $value . PHP_EOL;
        }

        echo $output . PHP_EOL;

        flush();
    }

    public static function prints(?string $str, int $padding = 4096)
    {
        if ($str) {
            foreach (preg_split('/(?<!^)(?!$)/u', (string) $str) as $word) {
                self::print($word, $padding);
            }
        }
    }

    public static function request(
        string $url,
        string $method,
        string $postFields,
        array $headers,
        callable $streamFunc,
        array $opts = []
    )
    {
        if ($method == 'GET') {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($posts);
            $posts = [];
        }

        $writeFunc = function ($curl, $data) use ($streamFunc) {

            $streamFunc($data);

            // 这里务必返回原文的长度
            // 否则流输出时校验会失败 Curl error: Failure writing output to destination
            return strlen($data);

        };

        $params = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => $opts['retries'] ?? 10,
            CURLOPT_TIMEOUT        => $opts['timeout'] ?? 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_WRITEFUNCTION  => $writeFunc,
            CURLOPT_POSTFIELDS     => $postFields,
        ];

        if (! $posts) {
            unset($params[CURLOPT_POSTFIELDS]);
        }

        $curl = curl_init();

        curl_setopt_array($curl, $params);
        $response = curl_exec($curl);

        if ($response === false || curl_errno($curl)) {
            throwx('CURL SSE ERROR | ' . curl_error($curl));
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($httpCode != 200) {
            throwx('CURL SSE ERROR | ' . $response . ' | CODE:' . $httpCode);
        }

        curl_close($curl);

        return $response;
    }

    public static function response(callable $callback)
    {
        ini_set('output_buffering', 'Off');
        ini_set('max_execution_time', 300);

        $headers = [
            'Content-type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ];

        return response()->stream($callback, 200, $headers);
    }
}
