<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->api(append: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                if ($e instanceof ValidationException) {
                    return \App\Helpers\ApiResponse::error(
                        $e->getMessage(),
                        \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY,
                        $e->errors()
                    );
                }

                if ($e instanceof AuthenticationException) {
                    return \App\Helpers\ApiResponse::error(
                        'Unauthenticated.',
                        \Illuminate\Http\Response::HTTP_UNAUTHORIZED
                    );
                }

                if ($e instanceof AuthorizationException) {
                    return \App\Helpers\ApiResponse::error(
                        'Unauthorized.',
                        \Illuminate\Http\Response::HTTP_FORBIDDEN
                    );
                }

                if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
                    $message = 'Resource not found.';

                    if ($e instanceof NotFoundHttpException && ! empty($e->getMessage())) {
                        $message = $e->getMessage();
                    }

                    return \App\Helpers\ApiResponse::error(
                        $message,
                        \Illuminate\Http\Response::HTTP_NOT_FOUND
                    );
                }

                // Default error
                $statusCode = 500;

                if ($e instanceof HttpExceptionInterface) {
                    $statusCode = $e->getStatusCode();
                }

                // If status code is not valid HTTP error code, default to 500
                if ($statusCode < 400 || $statusCode > 599) {
                    $statusCode = 500;
                }

                return \App\Helpers\ApiResponse::error(
                    $e->getMessage(),
                    $statusCode
                );
            }
        });
    })->create();
