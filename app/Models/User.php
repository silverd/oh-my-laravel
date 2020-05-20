<?php

namespace App\Models;

use Silverd\OhMyLaravel\Models\User as BaseUser;

class User extends BaseUser
{
    protected $hidden = [
        'password',
        'api_token',
    ];

    const
        GENDER_MAN   = 1,
        GENDER_WOMAN = 2;

    const GENDERES = [
        self::GENDER_MAN   => '男',
        self::GENDER_WOMAN => '女',
    ];
}
