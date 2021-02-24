<?php

namespace Silverd\OhMyLaravel\Services;

abstract class AbstractService
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        $this->init();
    }

    public function init()
    {

    }
}
