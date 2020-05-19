<?php

namespace Silverd\OhMyLaravel\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ImgUrls implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    public function set($model, $key, $value, $attributes)
    {
        $value = array_map('imgUrl', (array) $value);

        return json_encode($value);
    }
}
