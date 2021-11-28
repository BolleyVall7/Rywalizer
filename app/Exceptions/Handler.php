<?php

namespace App\Exceptions;

use App\Http\ErrorCodes\AuthErrorCode;
use App\Http\ErrorCodes\BaseErrorCode;
use App\Http\Libraries\FieldsConversion\FieldConversion;
use App\Http\Responses\JsonResponse;
use BadMethodCallException;
use Error;
use ErrorException;
use Exception;
use GuzzleHttp\Exception\ClientException;
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
                    FieldConversion::convertToCamelCase($throwable->getData()),
                    FieldConversion::convertToCamelCase($throwable->getMetadata()),
                );
                break;

            case BadMethodCallException::class:
                /** @var BadMethodCallException $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::INTERNAL_SERVER_ERROR(),
                    env('APP_DEBUG') ? FieldConversion::convertToCamelCase($throwable->getMessage()) : null
                );
                break;

            case ClientException::class:
                /** @var ClientException $throwable */

                JsonResponse::sendError(
                    AuthErrorCode::INVALID_CREDENTIALS_PROVIDED(),
                    FieldConversion::convertToCamelCase($throwable->getMessage())
                );
                break;

            case Error::class:
                /** @var Error $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::INTERNAL_SERVER_ERROR(),
                    env('APP_DEBUG') ? FieldConversion::convertToCamelCase($throwable->getMessage()) : null
                );
                break;

            case ErrorException::class:
                /** @var ErrorException $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::INTERNAL_SERVER_ERROR(),
                    env('APP_DEBUG') ? FieldConversion::convertToCamelCase($throwable->getMessage()) : null
                );
                break;

            case Exception::class:
                /** @var Exception $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::INTERNAL_SERVER_ERROR(),
                    env('APP_DEBUG') ? FieldConversion::convertToCamelCase($throwable->getMessage()) : null
                );
                break;

            case FatalError::class:
                /** @var FatalError $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::INTERNAL_SERVER_ERROR(),
                    env('APP_DEBUG') ? FieldConversion::convertToCamelCase($throwable->getMessage()) : null
                );
                break;

            case HttpException::class:
                /** @var HttpException $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::PERMISSION_DENIED(),
                    FieldConversion::convertToCamelCase($throwable->getMessage())
                );
                break;

            case MethodNotAllowedHttpException::class:
                /** @var MethodNotAllowedHttpException $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::INTERNAL_SERVER_ERROR(),
                    env('APP_DEBUG') ? FieldConversion::convertToCamelCase($throwable->getMessage()) : null
                );
                break;

            case NotFoundHttpException::class:
                /** @var NotFoundHttpException $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::INTERNAL_SERVER_ERROR(),
                    env('APP_DEBUG') ? FieldConversion::convertToCamelCase($throwable->getMessage()) : null
                );
                break;

            case QueryException::class:
                /** @var QueryException $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::INTERNAL_SERVER_ERROR(),
                    env('APP_DEBUG') ? FieldConversion::convertToCamelCase($throwable->errorInfo) : null
                );
                break;

            case ThrottleRequestsException::class:
                /** @var ThrottleRequestsException $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::LIMIT_EXCEEDED(),
                    FieldConversion::convertToCamelCase($throwable->getMessage())
                );
                break;

            case TypeError::class:
                /** @var TypeError $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::INTERNAL_SERVER_ERROR(),
                    env('APP_DEBUG') ? FieldConversion::convertToCamelCase($throwable->getMessage()) : null
                );
                break;

            case ValidationException::class:
                /** @var ValidationException $throwable */

                JsonResponse::sendError(
                    BaseErrorCode::FAILED_VALIDATION(),
                    FieldConversion::convertToCamelCase($throwable->errors())
                );
                break;

            default:
                JsonResponse::sendError(
                    BaseErrorCode::INTERNAL_SERVER_ERROR(),
                    env('APP_DEBUG') ? FieldConversion::convertToCamelCase($class) : null
                );
                break;
        }
    }
}
