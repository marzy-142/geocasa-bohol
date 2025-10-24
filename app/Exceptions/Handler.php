<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'prc_id',
        'business_permit',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Custom error reporting logic
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });

        // Handle API requests differently
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    /**
     * Handle API exceptions with proper JSON responses
     */
    protected function handleApiException(Throwable $e, Request $request)
    {
        $status = 500;
        $message = 'Internal Server Error';
        $errors = [];

        if ($e instanceof ValidationException) {
            $status = 422;
            $message = 'Validation Error';
            $errors = $e->errors();
        } elseif ($e instanceof NotFoundHttpException) {
            $status = 404;
            $message = 'Resource Not Found';
        } elseif ($e instanceof AccessDeniedHttpException) {
            $status = 403;
            $message = 'Access Denied';
        } elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {
            $status = 401;
            $message = 'Unauthenticated';
        }

        $response = [
            'success' => false,
            'message' => $message,
            'status' => $status,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        // Add debug information in development
        if (config('app.debug') && !($e instanceof ValidationException)) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ];
        }

        return response()->json($response, $status);
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, \Illuminate\Auth\AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'status' => 401,
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Create a response object from the given validation exception.
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($e->response) {
            return $e->response;
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'status' => 422,
                'errors' => $e->errors(),
            ], 422);
        }

        return redirect()->back()
            ->withInput($request->except($this->dontFlash))
            ->withErrors($e->errors());
    }
}