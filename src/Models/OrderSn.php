<?php

namespace Silverd\OhMyLaravel\Models;

class OrderSn extends AbstractModel
{
    protected $primaryKey = 'order_sn';
    protected $keyType = 'string';
    public $incrementing = false;
}
