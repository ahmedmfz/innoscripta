<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Helper\ApiResponseHelper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->expectsJson();
        });

        // 404: route not found, etc.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return ApiResponseHelper::returnErrorMessage('Object Not Found', JsonResponse::HTTP_NOT_FOUND);
        });

        // 404: missing Eloquent model
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            return ApiResponseHelper::returnErrorMessage('Object Not Found', JsonResponse::HTTP_NOT_FOUND);
        });

        // 405: wrong HTTP verb
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            return ApiResponseHelper::returnErrorMessage('Method Not Allowed', JsonResponse::HTTP_METHOD_NOT_ALLOWED);
        });

        // 403: authenticated but not authorized
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            return ApiResponseHelper::returnErrorMessage('access_denied', JsonResponse::HTTP_FORBIDDEN);
        });

        // 401: unauthenticated
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return ApiResponseHelper::returnErrorMessage('access_denied', JsonResponse::HTTP_UNAUTHORIZED);
        });

        // 500: fallback
        $exceptions->render(function (Throwable $e, Request $request) {
            $message = config('app.debug') ? $e->getMessage() : 'Server error';
            return ApiResponseHelper::returnErrorMessage($message, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        });

    })->create();
