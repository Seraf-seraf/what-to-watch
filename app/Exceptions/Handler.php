<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

        $this->renderable(function (\InvalidArgumentException $e, $request) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        });

        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            return response()->json([
                'error' => 'Нет доступа к дейсвтию!',
            ], 403);
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'error' => 'Запрашиваемая страница не существует',
            ], 404);
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 405);
        });

        $this->renderable(function (InvalidCredentialsException $e, $request) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 401);
        });

        $this->renderable(function (QueryException $e, $request) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        });
    }

    protected function unauthenticated(
        $request,
        AuthenticationException $exception
    ): Response|JsonResponse|RedirectResponse {
        return response()->json(['message' => 'Нет активной сессии'], 401);
    }
}
