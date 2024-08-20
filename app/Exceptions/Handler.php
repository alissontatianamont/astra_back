<?php

namespace App\Exceptions;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    protected function unauthenticated($request, AuthenticationException $exception)
{
    return response()->json([
        'message' => 'No estás autenticado. Por favor, inicia sesión para acceder a esta ruta.'
    ], 401);
}
public function render($request, Throwable $exception)
{
    if ($exception instanceof MethodNotAllowedHttpException) {
        return response()->json([
            'message' => 'Metodo no permitido.'
        ], 405);
    }

    return parent::render($request, $exception);
}
}
