<?php

namespace Silverd\OhMyLaravel\Middleware;

use Closure;
use Illuminate\Http\Request;

class ReqRespLog
{
    // 此方法必须有，虽然什么都不干
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        return $response;
    }

    public function terminate($request, $response)
    {
        $context = [
            'req_sn'    => $GLOBALS['_REQUEST_SN'],
            'req_args'  => [
                'path'    => $request->fullUrl(),
                'method'  => $request->method(),
                'header'  => $request->header(),
                'request' => $request->all(),
            ],
            'resp_body' => $response,
            'elapsed'   => round(microtime(true) - LARAVEL_START, 6),
        ];

        \Log::channel('req_resp')->info('API 请求响应', $context);
    }
}
