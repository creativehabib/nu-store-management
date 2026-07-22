<?php

use App\Http\Middleware\CheckIfApproved;
use App\Http\Middleware\CheckIfInstalled;
use App\Http\Middleware\EnsureApiTokenIsValid;
use App\Http\Middleware\EnsureApiUserTokenIsValid;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'approved' => CheckIfApproved::class,
            'api.token' => EnsureApiTokenIsValid::class,
            'api.user.token' => EnsureApiUserTokenIsValid::class,
        ]);
        $middleware->web(append: [
            SetLocale::class,
            CheckIfInstalled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (NotFoundHttpException $exception, Request $request): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => 'Not found.',
            ], 404);
        });
    })->create();
