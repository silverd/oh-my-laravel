<?php

namespace Silverd\OhMyLaravel\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use RuntimeException;

class ReqInterval
{
    public function handle(Request $request, Closure $next, int $seconds = 1, ?string $namespace = null)
    {
        $cacheKey = self::getUniqueKey($request, $namespace);

        if ($diffSecs = self::calcNextReqDiffSecs($cacheKey, $seconds)) {
            throws(__('Too frequent operation, please try again in :secs seconds', ['secs' => $diffSecs]), -2);
        }

        return $next($request);
    }

    // 返回下次请求仍需等待的秒数
    private static function calcNextReqDiffSecs(string $cacheKey, int $seconds = 1)
    {
        $currentTime = time();

        if (Cache::add($cacheKey, $currentTime, $seconds)) {
            return 0;
        }

        $lastReqTime = Cache::get($cacheKey);

        // 两次请求间隔不得少于N毫秒
        if ($lastReqTime && ($diffSecs = $lastReqTime + $seconds - $currentTime) > 0) {
            return $diffSecs;
        }

        return 0;
    }

    // 构造请求标识
    private static function getUniqueKey(Request $request, ?string $namespace = null)
    {
        if ($user = $request->user()) {
            $uniqueKey = sha1($user->getAuthIdentifier());
        }
        elseif ($route = $request->route()) {
            $uniqueKey = sha1($route->getDomain() . '|' . $request->ip());
        }
        else {
            throw new RuntimeException('Unable to generate the request signature. Route unavailable.');
        }

        $routeStr = sha1(strtolower($request->method() . ':' . $request->path()));

        return 'LastReqTime:' . $uniqueKey . ':' . $routeStr . ($namespace ? (':' . $namespace) : '');
    }
}
