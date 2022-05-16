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

    public function validate(array $credentials = [])
    {
        if (! $credentials['user']) {
            return false;
        }

        $mappings = config('auth.basic_auth.' . $credentials['field']);

        if ($credentials['user'] != $mappings['user'] || $credentials['password'] != $mappings['password']) {
            return false;
        }

        return true;
    }

    public function basic($field = 'default', $extraConditions = [])
    {
        $credentials = [
            'field'    => $field,
            'user'     => $this->request->getUser(),
            'password' => $this->request->getPassword(),
        ];

        if (! $this->validate($credentials)) {
            throw new UnauthorizedHttpException('Basic', 'Invalid credentials.');
        }
    }
}
