<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use App\Http\JsonResponseFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Log\LogManager as Log;
use Illuminate\Config\Repository as Config;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class Handler extends ExceptionHandler
{
    protected $jsonResponseFactory = null;
    protected $config = null;
    protected $log = null;

    public function __construct(Container $container, JsonResponseFactory $jsonResponseFactory, Config $config, Log $log)
    {
        $this->container = $container;
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->config = $config;
        $this->log = $log;
    }

    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    public function render($request, Exception $exception)
    {
        switch (get_class($exception)) {
            case AuthorizationException::class:
                return $this->jsonResponseFactory->error([], 403);
            case NotFoundHttpException::class:
            case ModelNotFoundException::class:
                return $this->jsonResponseFactory->error([], 404);
            case MethodNotAllowedHttpException::class:
                return $this->jsonResponseFactory->error([], 405);
            case ValidationException::class:
                return $this->jsonResponseFactory->error($exception->errors(), 422);
            case HttpException::class:
            case ThrottleRequestsException::class:
                return $this->jsonResponseFactory->error([], $exception->getStatusCode());
            case InvalidUploadTypeException::class:
            case DuplicateUploadException::class:
            case UploadNotFoundException::class:
                return $this->jsonResponseFactory->error([], 418);
            case InvalidSearchConditionsException::class:
            case DuplicateOfferContactException::class:
            case DuplicatePitchContactException::class:
                return $this->jsonResponseFactory->error([], 400);
            default:
                if ($this->config->get("app.env") == "local") {
                    throw $exception;
                } else {
                    $this->log->error($exception);
                    return $this->jsonResponseFactory->error([], 500);
                }
        }
    }
}
