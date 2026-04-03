<?php

namespace App\Exceptions;

use App\Helpers\ErrorHelper;
use App\Helpers\JsonResponseHelper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        AuthorizationException::class,
        NotFoundHttpException::class,
        ModelNotFoundException::class,
        MethodNotAllowedHttpException::class,
        ThrottleRequestsException::class,
        ExternalAPIException::class,
        SamePasswordException::class,
        HttpException::class,
    ];

    public function report(Throwable $exception)
    {
        /**
         * Although we do want to report ValidationExceptions the shouldReport
         * method will always return false. This section overrides the
         * $internalDontReport property which is responsible for that.
         */
        $class = get_class($exception);
        if (! $this->shouldReport($exception) && $class != ValidationException::class) {
            return;
        }
        switch ($class) {
            case ValidationException::class:
                /**
                 * This section logs validation errors for specific controllers
                 * and actions. Validation errors are stored in separate logs so
                 * as not to pollute the error logs.
                 */
                list($controller, $action) = get_controller_and_action_from_trace($exception->getTrace());
                $requireValidationLogging = [
                    // drafts
                    'DraftsController' => [
                        'updateAction',
                        'publishAction',
                    ],
                    // offers
                    'OffersController' => [
                        'createAction',
                        'updateAction',
                    ],
                    'OfferContactsController' => [
                        'createAction',
                    ],
                    'OfferDocumentsController' => [
                        'createAction',
                        'updateAction',
                    ],
                    // organisations
                    'OrganisationsController' => [
                        'createAction',
                        'updateAction',
                    ],
                    'OrganisationDocumentsController' => [
                        'createAction',
                        'updateAction',
                    ],
                    // pitches
                    'PitchesController' => [
                        'createAction',
                        'updateAction',
                    ],
                    'PitchContactsController' => [
                        'createAction',
                    ],
                    'PitchDocumentsController' => [
                        'createAction',
                        'updateAction',
                    ],
                    'CarbonCertificationsController' => [
                        'createAction',
                        'updateAction',
                    ],
                    'TreeSpeciesController' => [
                        'createAction',
                        'updateAction',
                    ],
                    'RestorationMethodMetricsController' => [
                        'createAction',
                        'updateAction',
                    ],
                ];
                if (! is_null($controller) &&
                    array_key_exists($controller, $requireValidationLogging) &&
                    in_array($action, $requireValidationLogging[$controller])
                ) {
                    $request = App::make('request');
                    Log::channel('validation')->error(
                        $exception->errors(),
                        [
                            'path_info' => $request->getPathInfo(),
                            'data' => $request->json()->all(),
                            'user_id' => Auth::user() ? Auth::user()->id : null,
                        ]
                    );
                }

                return;
            default:
                if (config('app.env') != 'local') {
                    App::make('sentry')->captureException($exception);
                    Log::error($exception);
                }

                return;
        }
    }

    public function render($request, Throwable $exception)
    {
        switch (get_class($exception)) {
            case AuthenticationException::class:
            case AuthorizationException::class:
                return JsonResponseHelper::error([], 403);
            case NotFoundHttpException::class:
            case ModelNotFoundException::class:
                return new JsonResponse($exception->getMessage(), 404);
            case MethodNotAllowedHttpException::class:
                return JsonResponseHelper::error([], 405);
            case ValidationException::class:
                return JsonResponseHelper::error($exception->errors(), 422);
            case ThrottleRequestsException::class:
                return JsonResponseHelper::error([], 429);
            case ExternalAPIException::class:
                $errors = ErrorHelper::create('*', 'external API', 'EXTERNAL_API', 'has returned a server error');

                return JsonResponseHelper::error($errors, 500);
            case SamePasswordException::class:
                $errors = ErrorHelper::create('password', 'new password', 'CUSTOM', 'must be different to the old password');

                return JsonResponseHelper::error($errors, 422);
            case HttpException::class:
                return JsonResponseHelper::error([], $exception->getStatusCode());
            case InvalidStatusException::class:
                return JsonResponseHelper::error($exception->getMessage(), 422);
            case MimeTypeNotAllowed::class:
            case UnreachableUrl::class:
                return JsonResponseHelper::error([[$exception->getMessage()]], 422);
            default:
                if (config('app.env') == 'local') {
                    return new Response($this->renderExceptionContent($exception), 500, ['Content-Type' => 'text/html']);
                } else {
                    return JsonResponseHelper::error([], 500);
                }
        }
    }
}
