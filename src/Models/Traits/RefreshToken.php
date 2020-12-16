<?php

namespace Silverd\OhMyLaravel\Models\Traits;

trait RefreshToken
{
    public function initToken()
    {
        if (! $this->api_token) {
            $this->refreshToken();
        }
    }

    public function refreshToken()
    {
        $this->api_token = $this->id . 'XZZ' . buildToken($this->id);
        $this->save();
    }
}
