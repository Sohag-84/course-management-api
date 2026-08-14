<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                $previous = $e->getPrevious();

                if ($previous instanceof ModelNotFoundException) {
                    $model = class_basename($previous->getModel());
                    return response()->json([
                        'success' => false,
                        'message' => "{$model} not found",
                    ], 404);
                }

                // Route নিজেই match হয়নি (ভুল URL) এমন case এর জন্য
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint not found',
                ], 404);
            }
        });
    })->create();
