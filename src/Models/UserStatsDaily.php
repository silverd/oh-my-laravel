<?php

namespace Silverd\OhMyLaravel\Models;

class UserStatsDaily extends AbstractModel
{
    protected $table = 'users_stats_daily';

    protected $hidden = [
        'id',
        'uid',
        'created_at',
        'updated_at',
    ];
}
