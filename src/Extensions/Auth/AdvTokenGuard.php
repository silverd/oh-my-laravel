<?php

/**
 * BigApiToken
 *
 * @author JiangJian <silverd@sohu.com>
 */

namespace Silverd\OhMyLaravel\Extensions\Auth;

use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class AdvTokenGuard extends TokenGuard
{
    /**
     * The user we last attempted to retrieve.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $lastAttempted;

    /**
     * Determine if the user matches the credentials.
     *
     * @param  mixed  $user
     */
    protected function hasValidCredentials($user, array $credentials): bool
    {
        return $user !== null && $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * Attempt to authenticate the user using the given credentials and return the token.
     *
     * @return bool|string
     */
    public function attempt(array $credentials = [])
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        if ($this->hasValidCredentials($user, $credentials)) {
            return $this->login($user);
        }

        return false;
    }

    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return string
     */
    public function login(AuthenticatableContract $user): string
    {
        $this->user = $user;

        // 刷新凭证
        $user->initToken();

        // 更新用户最后登录时间
        $user->refreshLastLogin();

        return $user->api_token;
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        $user = $this->user();

        // 刷新凭证
        $user->refreshToken();

        $this->user = null;
    }
}
