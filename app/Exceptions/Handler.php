<?php

namespace App\Exceptions;

use App\Http\ErrorCodes\AuthErrorCode;
use App\Http\ErrorCodes\BaseErrorCode;
use App\Http\Responses\JsonResponse;
use BadMethodCallException;
use Error;
use ErrorException;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use TypeError;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Metoda przechwytująca wszystkie napotkane wyjątki i odpowiednio je parsująca przed wysłaniem odpowiedzi zwrotnej.
     * 
     * @param \Illuminate\Http\Request $request
     * @param Throwable $throwable
     * 
     * @return void
     */
    public function render($request, Throwable $throwable): void {

        $class = get_class($throwable);

        switch ($class) {

            case ApiException::class:
                /** @var ApiException $throwable */

                JsonResponse::sendError(
                    $throwable->getErrorCode(),
                    $throwable->getData(),
                    $throwable->getMetadata(),
                );
                break;

            case BadMethodCallException::class:
            case BindingResolutionException::class:
            case Error::class:
            case ErrorException::class:
            case Exception::class:
            case FatalError::class:
            case MethodNotAllowedHttpException::class:
            case NotFoundHttpException::class:
            case TypeError::class:

                JsonResponse::sendError(
                    BaseErrorCode::INTERNAL_SERVER_ERROR(),
                    env('APP_DEBUG') ? [$throwable->getMessage(), $throwable->getFile(), $throwable->getLine()] : null
                );
                break;

            case ClientException::class:
                /** @var ClientException $throwable */

                JsonResponse::sendError(
                    AuthErrorCode::INVALID_CREDENTIALS_PROVIDED(),
                    $throwable->getMessage()
                );
                break;

            case HttpException::class:
                /** @var HttpException $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::PERMISSION_DENIED(),
                    $throwable->getMessage()
                );
                break;

            case QueryException::class:
                /** @var QueryException $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::INTERNAL_SERVER_ERROR(),
                    env('APP_DEBUG') ? [$throwable->errorInfo, $throwable->getFile(), $throwable->getLine()] : null
                );
                break;

            case ThrottleRequestsException::class:
                /** @var ThrottleRequestsException $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::LIMIT_EXCEEDED(),
                    $throwable->getMessage()
                );
                break;

            case ValidationException::class:
                /** @var ValidationException $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::FAILED_VALIDATION(),
                    $throwable->errors()
                );
                break;

            default:
                JsonResponse::sendError(
                    BaseErrorCode::INTERNAL_SERVER_ERROR(),
                    env('APP_DEBUG') ? $class : null
                );
                break;
        }
    }
}
