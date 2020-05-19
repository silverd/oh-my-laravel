<?php

/**
 * Http Basic Auth
 *
 * @author JiangJian <silverd@sohu.com>
 */

namespace Silverd\OhMyLaravel\Extensions\Auth;

use Illuminate\Http\Request;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class HttpBasicGuard implements Guard
{
    use GuardHelpers;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function user()
    {
        return null;
    }

    public function validate(array $credentials = [])
    {
        if (! $credentials['user']) {
            return false;
        }

        if ($credentials['user'] != config('auth.basic_auth.user') ||
            $credentials['password'] != config('auth.basic_auth.password')
        ) {
            return false;
        }

        return true;
    }

    public function basic()
    {
        $credentials = [
            'user' => $this->request->getUser(),
            'password' => $this->request->getPassword(),
        ];

        if (! $this->validate($credentials)) {
            throw new UnauthorizedHttpException('Basic', 'Invalid credentials.');
        }
    }
}
