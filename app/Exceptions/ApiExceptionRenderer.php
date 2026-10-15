<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Turns every exception raised under /api/* into the standard JSON envelope:
 *   { "success": false, "message": "...", "errors": { field: [..] } }
 *
 * Unexpected errors are still logged by Laravel's exception handler before they
 * reach this class; customers only ever see a generic message (details only when APP_DEBUG=true).
 */
class ApiExceptionRenderer
{
    public static function render(Throwable $e): JsonResponse
    {
        return match (true) {
            $e instanceof ValidationException => self::json('Please check the highlighted fields.', 422, $e->errors()),
            $e instanceof AuthenticationException => self::json('Your session has expired. Please sign in again.', 401),
            $e instanceof AuthorizationException, $e instanceof AccessDeniedHttpException => self::json('You do not have permission to do that.', 403),
            $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException => self::json('Not found.', 404),
            $e instanceof TokenMismatchException => self::json('Your session has expired. Please refresh the page and try again.', 419),
            $e instanceof ThrottleRequestsException => self::json('Too many requests. Please wait a moment and try again.', 429, null, $e->getHeaders()),
            $e instanceof HttpExceptionInterface => self::json(self::httpMessage($e->getStatusCode()), $e->getStatusCode(), null, $e->getHeaders()),
            default => self::json('Something went wrong on our side. Please try again shortly.', 500, null, [], $e),
        };
    }

    private static function httpMessage(int $status): string
    {
        return match ($status) {
            400 => 'The request could not be understood.',
            413 => 'The upload is too large.',
            419 => 'Your session has expired. Please refresh the page and try again.',
            429 => 'Too many requests. Please wait a moment and try again.',
            default => $status >= 500 ? 'Something went wrong on our side. Please try again shortly.' : 'The request could not be completed.',
        };
    }

    /**
     * @param  array<string, array<int, string>>|null  $errors
     * @param  array<string, string>  $headers
     */
    private static function json(string $message, int $status, ?array $errors = null, array $headers = [], ?Throwable $debug = null): JsonResponse
    {
        $body = ['success' => false, 'message' => $message];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        if ($debug !== null && config('app.debug')) {
            $body['debug'] = $debug->getMessage();
        }

        return response()->json($body, $status, $headers);
    }
}
