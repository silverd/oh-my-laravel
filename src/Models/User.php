<?php

namespace Silverd\OhMyLaravel\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Silverd\OhMyLaravel\Models\Traits\RefreshToken;
use Silverd\OhMyLaravel\Models\Traits\RefreshLastLogin;

class User extends AbstractModel implements AuthenticatableContract
{
    use Authenticatable, Notifiable, RefreshToken, RefreshLastLogin;

    protected $table = 'users';

    protected $hidden = [
        'password',
        'api_token',
        'session_key',
    ];

    // 我的每日数据统计
    public function statsDaily()
    {
        return $this->hasMany(UserStatsDaily::class, 'uid');
    }

    // 我的每日数据统计（累加）
    public function incrStatsDaily(array $incrs, array $updated = [])
    {
        $pk = [
            'today' => date('Y-m-d'),
        ];

        return $this->statsDaily()
            ->firstOrCreate($pk)
            ->increments($incrs, $updated);
    }
}
