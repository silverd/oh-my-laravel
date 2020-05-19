<?php

namespace Silverd\OhMyLaravel\Models\Traits;

trait RefreshToken {

    // 刷新凭证
    public function refreshToken()
    {
        $this->api_token = $this->id . 'XZZ' . buildToken($this->id);
        $this->save();
    }
}
