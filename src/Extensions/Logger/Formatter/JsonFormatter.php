<?php

namespace Silverd\OhMyLaravel\Extensions\Logger\Formatter;

use Monolog\Formatter\JsonFormatter as BaseJsonFormatter;

class JsonFormatter extends BaseJsonFormatter
{
    protected int $maxNormalizeDepth = 20;
}
