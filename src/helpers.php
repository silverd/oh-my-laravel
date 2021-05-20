<?php

if (! function_exists('vd')) {
    function vd($s, $exit = true)
    {
        echo '<pre>';
        var_dump($s);
        echo '</pre>';
        $exit && exit();
    }
}

if (! function_exists('pr')) {
    function pr($s, $exit = true)
    {
        echo '<pre>';
        print_r($s);
        echo '</pre>';
        $exit && exit();
    }
}


if (! function_exists('throws')) {
    function throws($message, $code = -1)
    {
        throw new \Silverd\OhMyLaravel\Exceptions\UserException($message, $code);
    }
}

if (! function_exists('throwx')) {
    function throwx($message, $code = -9999)
    {
        throw new \Silverd\OhMyLaravel\Exceptions\ThirdApiException($message, $code);
    }
}

if (! function_exists('ok')) {
    function ok($message = 'OK', $data = [])
    {
        return response()->output($message, 0, $data);
    }
}

if (! function_exists('ago')) {
    function ago(int $seconds)
    {
        return now()->subSeconds($seconds);
    }
}

if (! function_exists('imgUrl')) {
    function imgUrl($imgUrl)
    {
        if (! $imgUrl) {
            return '';
        }

        if (strpos($imgUrl, 'http') === 0) {
            return $imgUrl;
        }

        return \Storage::url($imgUrl);
    }
}

if (! function_exists('buildToken')) {
    function buildToken($uniqueId = null)
    {
        return sha1(uniqid($uniqueId) . mt_rand(1, 10000));
    }
}

if (! function_exists('setEmptyArrayToNull')) {
    function setEmptyArrayToNull(array $array)
    {
        if (! $array) {
            return null;
        }

        foreach ($array as $key => &$value) {
            if ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
                $value = $value->toArray();
            }
            if (is_array($value)) {
                if (! $value) {
                    $value = null;
                }
                else {
                    $func = __FUNCTION__;
                    $value = $func($value);
                }
            }
        }

        return $array;
    }
}

if (! function_exists('toJson')) {
    function toJson($source)
    {
        return response()->json($source, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

if (! function_exists('jsonEncode')) {
    function jsonEncode($source)
    {
        return json_encode($source, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

if (! function_exists('percentage')) {
    function percentage($decimal)
    {
        return $decimal === null ? null : sprintf('%.2f', round($decimal * 100, 2)) . '%';
    }
}

if (! function_exists('fetchImg')) {
    // 获取 URL 资源的二进制流
    function fetchImg(string $imgUrl, int $timeout = 30)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $imgUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $contents = curl_exec($ch);
        curl_close($ch);

        return $contents;
    }
}


if (! function_exists('nowTimeMs')) {
    // 当前毫秒时间
    function nowTimeMs()
    {
        return now()->format('Y-m-d H:i:s.u');
    }
}

if (! function_exists('nowMsTimestamp')) {
    function nowMsTimestamp()
    {
        return str_replace('.', '', number_format(microtime(true), 3, '.', ''));
    }
}

if (! function_exists('calcElapsedMs')) {
    function calcElapsedMs(string $dateMs)
    {
        if (! $dateMs) {
            return 0;
        }

        $tmp = explode('.', $dateMs);
        $ms = strtotime($tmp[0]) . '.' . $tmp[1];

        return round(microtime(true) - $ms, 3);
    }
}

if (! function_exists('getImgThumbUrl')) {
    /**
     * OSS 图片缩略图
     *
     * @see https://help.aliyun.com/document_detail/44688.html
     */
    function getImgThumbUrl(string $imgUrl, int $w = 750, int $h = 750, int $quality = 100, string $mode = 'm_fill')
    {
        $imgUrl = imgUrl($imgUrl);

        $con = strpos($imgUrl, '?') === false ? '?' : rawurlencode('/');
        $imgUrl .= $con . 'x-oss-process=image/resize,';

        $params = [];

        if ($mode !== null) {
            $params[] = $mode;
        }

        if ($w !== null) {
            $params[] = 'w_' . $w;
        }

        if ($h !== null) {
            $params[] = 'h_' . $h;
        }

        $imgUrl .= implode(',', $params);

        if ($quality !== null) {
            $imgUrl .= '/quality,q_' . $quality;
        }

        return $imgUrl;
    }
}

if (! function_exists('storageByStream')) {
    // 将文件二进制流转存到 COS 并返回URL
    function storageByStream(string $dirName, string $stream, string $extension = '.jpg', string $disk = '')
    {
        if (! $stream) {
            return '';
        }

        $imgPath = getStoragePath($dirName, $extension);

        $stoarge = \Storage::disk($disk ?: null);

        // 将图片存入 COS
        $stoarge->put($imgPath, $stream);
        $ourImgUrl = $stoarge->url($imgPath);

        return $ourImgUrl;
    }
}

if (! function_exists('storageByBase64')) {
    // 将 base64 转存到 COS 并返回URL
    function storageByBase64(string $dirName, string $base64, string $extension = '.jpg', string $disk = '')
    {
        if (! $base64) {
            return '';
        }

        $imgStream = base64_decode($base64);

        return storageByStream($dirName, $imgStream, $extension, $disk);
    }
}

if (! function_exists('storageByUrl')) {
    // 将远程图片转存到 COS 并返回URL
    function storageByUrl(string $dirName, string $imgUrl, string $extension = '.jpg', string $disk = '')
    {
        if (! $imgUrl) {
            return '';
        }

        $imgStream = fetchImg($imgUrl);

        return storageByStream($dirName, $imgStream, $extension, $disk);
    }
}

if (! function_exists('storageByUrlSilent')) {
    function storageByUrlSilent(...$params)
    {
        $url = '';

        try {
            $url = storageByUrl(...$params);
        } catch (\Throwable $e) {
            // do nothing
        }

        return $url;
    }
}

if (! function_exists('getStoragePath')) {
    function getStoragePath(string $dirName, string $extension = '.jpg')
    {
        return $dirName . '/' . date('YmdH') . '/' . \Str::random(32) . $extension;
    }
}

if (! function_exists('getTokenForRequest')) {
    function getTokenForRequest($inputKey)
    {
        $request = request();

        $token = $request->query($inputKey);

        if (empty($token)) {
            $token = $request->input($inputKey);
        }

        if (empty($token)) {
            $token = $request->bearerToken();
        }

        if (empty($token)) {
            $token = $request->getPassword();
        }

        return $token;
    }
}

if (! function_exists('resolveRequestSignature')) {
    function resolveRequestSignature()
    {
        $request = request();

        if ($user = $request->user()) {
            return sha1($user->getAuthIdentifier());
        }

        if ($route = $request->route()) {
            return sha1($route->getDomain() . '|' . $request->ip());
        }

        throw new \RuntimeException('Unable to generate the request signature. Route unavailable.');
    }
}

if (! function_exists('getHourKey')) {
    function getHourKey(string $date, int $hour)
    {
        return $date . ' '. getHourValue($hour);
    }
}

if (! function_exists('getHourValue')) {
    function getHourValue(int $hour)
    {
        return (strlen($hour) == 2 ? '' : '0') . $hour . ':00';
    }
}

if (! function_exists('getAllHours')) {
    function getAllHours(?string $date = null)
    {
        if (! $date || $date == date('Y-m-d')) {
            return [0, date('G')];
        }

        return [0, 23];
    }
}

if (! function_exists('floorx')) {
    // 舍去法保留N位小数
    function floorx(float $val, int $precision = 2)
    {
        $multipe = pow(10, $precision);

        return number_format(floor($val * $multipe) / $multipe, $precision, '.', '');
    }
}

if (! function_exists('_T')) {
    // 替换字符串中的变量
    function _T(string $message, array $vars = [])
    {
        if (! $vars) {
            return $message;
        }

        $keys = $values = [];

        foreach ($vars as $key => $value) {
            if (is_scalar($value)) {
                $keys[] = '{' . $key . '}';
                $values[] = $value;
            }
        }

        return str_replace($keys, $values, $message);
    }
}

if (! function_exists('getFullException')) {
    function getFullException(\Throwable $e)
    {
        return [
            'code'    => $e->getCode(),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ];
    }
}

if (! function_exists('guzHttpRequest')) {
    function guzHttpRequest(
        string $url,
        $params,
        string $method = 'POST',
        string $format = null,
        array $headers = [],
        string $respType = 'JSON',
        array $guzConfig = []
    ) {
        // @see http://guzzle-cn.readthedocs.io/zh_CN/latest
        $http = new \GuzzleHttp\Client(['verify' => false, 'headers' => $headers] + $guzConfig);

        $data = [$method == 'POST' ? 'form_params' : 'query' => $params];

        if ($format == 'JSON') {
            $data = ['json' => $params];
        }
        elseif ($format == 'RAW') {
            $data = ['body' => $params];
        }
        // 完全自定义
        elseif ($format == 'CUST') {
            $data = $params;
        }

        // 当前时间
        $nowMs = microtime(true);

        // 记录请求报文
        \Log::channel('api_request')->info('req', [
            'req_url'  => $url,
            'req_body' => $data,
        ]);

        try {

            $response = $http->request($method, $url, $data);

            $respCode = $response->getStatusCode();
            $respBody = $response->getBody()->getContents();

            // 记录响应报文（正常）
            \Log::channel('api_request')->info('resp', [
                'resp_status' => $respCode,
                'resp_body'   => $respBody,
                'elapsed'     => round(microtime(true) - $nowMs, 6),
            ]);
        }

        catch (\Throwable $e) {

            // 记录响应报文（异常）
            \Log::channel('api_request')->info('resp_err', [
                'exception' => getFullException($e),
                'elapsed'   => round(microtime(true) - $nowMs, 6),
            ]);

            throw $e;
        }

        $result = null;

        if ($respType === 'JSON' && $respBody) {
            $result = json_decode($respBody, true);
            if (($result === false || $result === null) && json_last_error() !== JSON_ERROR_NONE) {
                throwx('解析响应 JSON 异常：' . json_last_error_msg());
            }
        }

        return [$result, $respBody, $respCode];
    }
}


if (! function_exists('cacheRemember')) {
    // 缓存结果集
    function cacheRemember(array $params, callable $callback, string $store = 'database')
    {
        $cacheKey = md5(serialize($params));

        $result = \Cache::store($store)->rememberForever($cacheKey, function () use ($callback, $params) {
            return call_user_func($callback, $params);
        });

        return $result;
    }
}

if (! function_exists('base64Img')) {
    function base64Img($imgStream)
    {
        return 'data:image/png;base64, ' . base64_encode($imgStream);
    }
}

if (! function_exists('base64ImgWithCache')) {
    function base64ImgWithCache(string $imgUrl, int $ttlSecs = 0)
    {
        if (! $imgUrl) {
            return '';
        }

        $ttlSecs = $ttlSecs ?: 86400 * 7;

        return \Cache::remember('base64Img:' . md5($imgUrl), $ttlSecs, function () use ($imgUrl) {
            return base64Img(fetchImg($imgUrl));
        });
    }
}

if (! function_exists('buildSignature')) {
    function buildSignature(array $params, string $secretKey)
    {
        // 键名升序
        ksort($params);

        $strs = [];

        foreach ($params as $key => $value) {
            $strs[] = $key . '=' . (is_array($value) ? json_encode($value) : $value);
        }

        // 拼接待签名字符串
        $paramStr = implode('&', $strs);

        // 构造签名
        $signStr = base64_encode(hash_hmac('sha256', $paramStr, $secretKey, true));

        return urlencode($signStr);
    }
}

if (! function_exists('bcArraySum')) {
    function bcArraySum(array $nums, int $decimal = 2)
    {
        $sum = 0;

        foreach ($nums as $num) {
            $sum = bcadd($sum, $num, $decimal);
        }

        return $sum;
    }
}
