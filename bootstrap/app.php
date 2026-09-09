<?php

use App\Documents\UnknownTemplateException;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // An unknown document type in a URL is a 404, not a 500.
        $exceptions->map(fn (UnknownTemplateException $e) => new NotFoundHttpException($e->getMessage(), $e));

        // The PDF routes are called with fetch and must answer a failure with
        // JSON: a redirect would be followed and rendered as if it were a
        // document. Accept lists application/pdf first, so expectsJson() alone
        // does not cover them.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*')
                || $request->is('pdf/*')
                || $request->expectsJson(),
        );
    })->create();
