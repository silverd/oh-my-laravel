<?php

namespace Silverd\OhMyLaravel\Services;

class OpenSignService
{
    public static function buildSign(array $params, string $secretKey)
    {
        return buildSignature($params, $secretKey);
    }

    // 验证签名
    public static function verifySign(array $params, string $secretKey, string $signField = 'sign')
    {
        \Validator::make($params, [
            'nonce_str' => 'required|string',
            'timestamp' => 'required|string',
            $signField  => 'required|string',
        ])->validate();

        $signature = $params[$signField];

        // signature 不参与签名
        unset($params[$signField]);

        // 构造签名
        $signed = self::buildSign($params, $secretKey);

        if (urlencode($signature) != $signed && $signature != $signed) {
            throws('对称签名验证失败 =_=');
        }
    }
}
