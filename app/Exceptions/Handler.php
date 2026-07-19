<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\JsonResponse;

class Handler extends ExceptionHandler
{
    // ... existing code ...

    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'data' => null,
                'errors' => [
                    'auth' => ['You are not authenticated. Please login.'],
                ],
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'data' => null,
                    'errors' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                    'data' => null,
                    'errors' => [
                        'resource' => ['The requested resource was not found.'],
                    ],
                ], 404);
            }

            if ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Route not found',
                    'data' => null,
                    'errors' => [
                        'route' => ['The requested route was not found.'],
                    ],
                ], 404);
            }

            if ($exception instanceof MethodNotAllowedHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Method not allowed',
                    'data' => null,
                    'errors' => [
                        'method' => ['The HTTP method is not allowed for this route.'],
                    ],
                ], 405);
            }

            // For production, hide detailed error messages
            if (!app()->environment('local')) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred',
                    'data' => null,
                    'errors' => [
                        'error' => ['Something went wrong. Please try again later.'],
                    ],
                ], 500);
            }
        }

        return parent::render($request, $exception);
    }
}