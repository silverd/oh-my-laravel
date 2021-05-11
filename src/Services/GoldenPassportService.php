<?php

namespace Silverd\OhMyLaravel\Services;

class GoldenPassportService
{
    const BASE_URL = 'https://sh-passport.wetax.com.cn';

    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    // 获取用户列表
    public function getUserList(int $page = 1, int $pageSize = 20)
    {
        $result = $this->request('/api/users', 'GET', [
            'page'      => $page,
            'page_size' => $pageSize,
        ]);

        return $result['users']['data'];
    }

    protected function request(string $uri, string $method = 'POST', array $params = [])
    {
        $result = guzHttpRequest(self::BASE_URL . $uri, $params, $method, null, [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ]);

        if ($result[0]['code'] != 0) {
            throw new \Exception('GoldenPassport Error：' . $result[0]['message']);
        }

        return $result[0]['data'];
    }

    protected function getAccessToken()
    {
        $cacheKey = 'GoldenPassport:Client:AccessToken';

        if ($accessToken = \Cache::get($cacheKey)) {
            return $accessToken;
        }

        $result = guzHttpRequest(self::BASE_URL . '/oauth/token', [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
        ]);

        \Cache::put($cacheKey, $result[0]['access_token'], $result[0]['expires_in']);

        return $result[0]['access_token'];
    }
}
