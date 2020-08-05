<?php

namespace Silverd\OhMyLaravel\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        UserException::class,
        RouteNotFoundException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Api 异常响应
        $isApiRequest = $request->is(['api/*']) || $request->expectsJson();

        if ($isApiRequest && $request->header('x-debug-mode') != 2) {

            $resp = $this->handleApiException($request, $exception);

            return response()->output($resp['message'], $resp['code']);
        }

        return parent::render($request, $exception);
    }

    protected function handleApiException($request, Throwable $exception)
    {
        $code = $exception->getCode() ?: -1;
        $message = $exception->getMessage() ?: 'Oops';

        if ($exception instanceof UserException) {
            return compact('code', 'message');
        }

        if ($exception instanceof NotFoundHttpException) {
            header('HTTP/2 404 Not Found');
            exit('404 Not Found');
        }

        if ($exception instanceof ModelNotFoundException) {
            return [
                'code'    => $code,
                'message' => '找不到指定 ' . class_basename($exception->getModel()) . ' 记录',
            ];
        }

        if ($exception instanceof AuthenticationException) {
            return [
                'code'    => -1000,
                'message' => '登录状态已过期，请重新登录',
            ];
        }

        if ($exception instanceof AccessDeniedHttpException ||
           ($exception instanceof HttpException && $exception->getStatusCode() == 403)
        ) {
            return [
                'code'    => -403,
                'message' => $message,
            ];
        }

        if ($exception instanceof ValidationException) {
            return [
                'code'    => -1,
                'message' => current(\Arr::flatten($exception->errors())),
            ];
        }

        if ($request->header('x-debug-mode') == 1) {
            $message .= ' - ' . $exception->__toString();
        }
        else {
            $exceptionClass = get_class($exception);
            if ($exceptionClass != 'Exception') {
                $message .= ' - ' . $exceptionClass;
            }
        }

        return compact('code', 'message');
    }
}
