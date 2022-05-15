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

    public function validate(array $credentials = [], array $mappings)
    {
        if (! $credentials['user']) {
            return false;
        }

        if ($credentials['user'] != $mappings['user'] || $credentials['password'] != $mappings['password']) {
            return false;
        }

        return true;
    }

    public function basic($field = 'default', $extraConditions = [])
    {
        $credentials = [
            'user' => $this->request->getUser(),
            'password' => $this->request->getPassword(),
        ];

        $mappings = config('auth.basic_auth.' . $field);

        if (! $this->validate($credentials, $mappings)) {
            throw new UnauthorizedHttpException('Basic', 'Invalid credentials.');
        }
    }
}
