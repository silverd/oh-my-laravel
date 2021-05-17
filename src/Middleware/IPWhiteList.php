<?php

namespace Silverd\OhMyLaravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class IPWhiteList
{
    public function handle(Request $request, Closure $next, string $ipKey)
    {
        if (! $serverIps = config($ipKey)) {
            return $next($request);
        }

        if (! \App::environment('production')) {
            return $next($request);
        }

        $ip = $request->getClientIp();

        if (! IpUtils::checkIp($ip, $serverIps)) {
            throws('来源 ' . $ip . ' 为非法访问');
        }

        return $next($request);
    }
}

