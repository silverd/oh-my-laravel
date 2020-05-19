<?php

namespace App\Exceptions;

use Throwable;
use Silverd\OhMyLaravel\Exceptions\Handler as BaseExceptionHandler;

class Handler extends BaseExceptionHandler
{
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }
}
