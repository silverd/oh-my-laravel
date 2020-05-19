<?php

namespace Silverd\OhMyLaravel\Models\Traits;

use Silverd\OhMyLaravel\Models\UserLoginLog;

trait RefreshLastLogin {

    // 记录用户最后登录信息
    public function refreshLastLogin()
    {
        UserLoginLog::create([
            'uid'       => $this->id,
            'api_token' => $this->api_token,
            'ip'        => \Request::ip(),
        ]);
    }
}
