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

        $store = \Storage::disk($disk ?: null);

        // 将图片存入 COS
        $store->put($imgPath, $stream);
        $ourImgUrl = $store->url($imgPath);

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

if (! function_exists('roundx')) {
    // 舍去法保留N位小数
    function roundx(float $val, int $precision = 2)
    {
        return number_format(round($val, $precision), $precision, '.', '');
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

if (! function_exists('_S')) {
    // 替换字符串中的变量
    function _S(string $message, array $vars = [])
    {
        if (! $vars) {
            return $message;
        }

        $keys = $values = [];

        foreach ($vars as $key => $value) {
            if (is_scalar($value)) {
                $keys[] = '${' . $key . '}';
                $values[] = $value;
            }
        }

        return str_replace($keys, $values, $message);
    }
}

if (! function_exists('getSimpleException')) {
    function getSimpleException(\Throwable $e)
    {
        $message = $e->getMessage();

        if (! $e instanceof \Silverd\OhMyLaravel\Exceptions\UserException) {
            // 系统级异常输出具体位置
            $message .= '@' . $e->getFile() . ':' . $e->getLine();
        }

        return $message;
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

if (! function_exists('humanExceptionText')) {
    function humanExceptionText(\Throwable $e)
    {
        if ($e instanceof \Silverd\OhMyLaravel\Exceptions\UserException) {
            return $e->getMessage();
        }

        if (\App::environment('production')) {
            return '我的脑子已经转不过来了，请稍候再提问 ...';
        }

        return $e->getMessage() . '@' . $e->getFile() . ':' . $e->getLine();
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
        $headers['x-request-sn'] = $GLOBALS['_REQUEST_SN'];

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

        // 请求流水号
        $requestSn = \Str::orderedUuid();

        // 记录请求报文
        \Log::channel('api_request')->info('req:' . $requestSn, [
            'req_url'  => $url,
            'req_body' => $data,
        ]);

        try {

            $response = $http->request($method, $url, $data);

            $respCode = $response->getStatusCode();
            $respBody = $response->getBody()->getContents();

            // 记录响应报文（正常）
            \Log::channel('api_request')->info('resp:' . $requestSn, [
                'resp_status' => $respCode,
                'resp_body'   => $respBody,
                'elapsed'     => round(microtime(true) - $nowMs, 6),
            ]);
        }

        catch (\Throwable $e) {

            // 记录响应报文（异常）
            \Log::channel('api_request')->info('resp_err:' . $requestSn, [
                'exception' => getFullException($e),
                'elapsed'   => round(microtime(true) - $nowMs, 6),
            ]);

            throw $e;
        }

        $result = null;

        if ($respBody && strpos($respType, 'JSON') !== false) {
            $result = json_decode($respBody, true);
            if (strpos($respType, 'NO_CHECK') === false) {
                if (($result === false || $result === null) && json_last_error() !== JSON_ERROR_NONE) {
                    throwx('解析响应 JSON 异常：' . json_last_error_msg());
                }
            }
        }

        return [$result, $respBody, $respCode];
    }
}

if (! function_exists('loggingInOut')) {
    function loggingInOut(string $channel, array $args, callable $callback)
    {
        // 当前时间
        $nowMs = microtime(true);

        // 请求流水号
        $requestSn = \Str::orderedUuid();

        // 记录请求报文
        \Log::channel($channel)->info('req:' . $requestSn, [
            'req_args' => $args,
        ]);

        try {

            $response = call_user_func_array($callback, $args);

            // 记录响应报文（正常）
            \Log::channel($channel)->info('resp:' . $requestSn, [
                'resp_body' => $response,
                'elapsed'   => round(microtime(true) - $nowMs, 6),
            ]);
        }

        catch (\Throwable $e) {

            \Log::channel($channel)->info('resp_err:' . $requestSn, [
                'exception' => getFullException($e),
                'elapsed'   => round(microtime(true) - $nowMs, 6),
            ]);

            throw $e;
        }

        return $response;
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

        return \Cache::remember('base64XImg:' . md5($imgUrl), $ttlSecs, function () use ($imgUrl) {
            return base64Img(imgUrlToStream($imgUrl));
        });
    }
}

if (! function_exists('imgUrlToStream')) {
    function imgUrlToStream(string $imgUrl)
    {
        $siteUrl = config('app.url');

        // 本地图片
        if (strpos($imgUrl, $siteUrl) === 0) {

            $imgPath = str_replace($siteUrl, '', $imgUrl);

            return file_get_contents(public_path($imgPath));
        }

        // 远程图片
        else {

            return fetchImg($imgUrl);
        }
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

if (! function_exists('clearZero')) {
    function clearZero($value, $zero = '')
    {
        return floatval($value) == 0 ? $zero : $value;
    }
}

if (! function_exists('bcPlus')) {
    function bcPlus(...$args)
    {
        $return = 0;

        foreach ($args as $value) {
            $return = bcadd($return, $value, 2);
        }

        return $return;
    }
}

if (! function_exists('scientificToNum')) {
    // 科学计数法转化为数字
    function scientificToNum(string $num)
    {
        if (! is_numeric($num) || stripos($num, 'e') === false) {
            return $num;
        }

        $a = explode('e', strtolower($num));

        return bcmul($a[0], bcpow(10, $a[1]));
    }
}

if (! function_exists('array_map_recursive')) {
    function array_map_recursive(callable $func, array $array)
    {
        return filter_var($array, \FILTER_CALLBACK, ['options' => $func]);
    }
}

if (! function_exists('decimalToNumber')) {
    function decimalToNumber(array $array, int $precision = 6)
    {
        return array_map_recursive(function ($value) use ($precision) {
            return is_numeric($value) ? round($value, $precision) : $value;
        }, $array);
    }
}

if (! function_exists('trimBom')) {
    // 过滤文本中的BOM头
    function trimBom(string $str)
    {
        $charset[1] = substr($str, 0, 1);
        $charset[2] = substr($str, 1, 1);
        $charset[3] = substr($str, 2, 1);

        if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
            return substr($str, 3);
        }

        return $str;
    }
}

if (! function_exists('mapPagedList')) {
    function mapPagedList($list, callable $callback)
    {
        $stats = is_array($list) ? $list : $list->toArray();

        foreach ($stats['data'] ?? [] as $key => $one) {
            $stats['data'][$key] = $callback((array) $one);
        }

        if (isset($stats['bottom'])) {
            $stats['bottom'] = $callback((array) $stats['bottom']);
        }

        return $stats;
    }
}

if (! function_exists('arrayMap')) {
    function arrayMap(array $array, callable $func)
    {
        return array_map($func, $array);
    }
}

if (! function_exists('lowerArrayKey')) {
    function lowerArrayKey(array $one)
    {
        $newOne = [];

        foreach ($one as $key => $value) {
            $newOne[strtolower($key)] = $value;
        }

        return $newOne;
    }
}

if (! function_exists('isNumeric')) {
    function isNumeric($value)
    {
        return is_numeric($value) && strlen(intval($value)) <= 13;
    }
}

if (! function_exists('filePathLocalized')) {
    function filePathLocalized(string $dirName, string $filePath, string $extension = '.jpg')
    {
        if (strpos($filePath, '/') === 0) {
            return $filePath;
        }

        $store = \Storage::disk('local');

        if (strpos($filePath, 'http') === 0) {
            $fileStream = fetchImg($filePath);
            $filePath = getStoragePath($dirName, $extension);
            $store->put($filePath, $fileStream);
        }

        return $store->path($filePath);
    }
}

if (! function_exists('isShouldRunButNot')) {
    // 是否轮到它该运行了，但却没运行
    function isShouldRunButNot(string $cronExpr, string $lastRunAt)
    {
        $cron = new \Cron\CronExpression($cronExpr);

        // 应运行时间
        $dueRunAt = $cron->getNextRunDate(date('Y-m-d'))->format('Y-m-d H:i:s');

        // 今日无需运行
        if (! \Carbon\Carbon::parse($dueRunAt)->isToday()) {
            return false;
        }

        return [
            $lastRunAt >= $dueRunAt,
            $dueRunAt,
        ];
    }
}
