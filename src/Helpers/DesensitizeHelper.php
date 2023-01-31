<?php

namespace Silverd\OhMyLaravel\Helpers;

class DesensitizeHelper
{
    // @see https://www.tapd.cn/31856179/markdown_wikis/show/#1131856179001002667
    public static function mask(string $type, string $string)
    {
        switch ($type) {

            case 'PHONE':

                return \Str::mask($string, '*', 7);

            case 'IDCARD':

                preg_match('/\d+/', $string, $matched);

                $string = $matched[0];
                $length = strlen($string);

                if ($length == 15) {
                    return \Str::mask($string, '*', -3, 3);
                }

                return \Str::mask($string, '*', -4, 4);

            case 'NAME':

                return \Str::mask($string, '*', 1);

            case 'ADDRESS':

                return \Str::mask($string, '*', 6);

            case 'DELETE':

                return '';

            case 'BANK':

                return \Str::mask($string, '*', 6);

            case 'UNIQUE':

                return md5($string);

            case 'SINGLE_ASTERISK':

                return '*';
        }
    }
}
