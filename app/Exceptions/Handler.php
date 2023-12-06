<?php

namespace App\Exceptions;

use App\Exceptions\Terrafund\InvalidMorphableModelException;
use App\Exceptions\Terrafund\InvalidTerrafundFileUploadException;
use App\Exceptions\Terrafund\NoProgrammeFilesException;
use App\Helpers\ErrorHelper;
use App\Helpers\JsonResponseHelper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        AuthorizationException::class,
        FailedLoginException::class,
        NotFoundHttpException::class,
        ModelNotFoundException::class,
        MethodNotAllowedHttpException::class,
        ThrottleRequestsException::class,
        InvisiblePitchException::class,
        InvisibleOfferException::class,
        DuplicateInterestException::class,
        InvalidUploadTypeException::class,
        MismatchingDraftTypeException::class,
        InviteAlreadyAcceptedException::class,
        DuplicateUploadException::class,
        UploadNotFoundException::class,
        ExternalAPIException::class,
        CorruptedUploadException::class,
        InvalidSearchConditionsException::class,
        InvalidFrameworkInviteCodeException::class,
        InvalidProgrammeFileUploadException::class,
        InvalidSiteFileUploadException::class,
        InvalidMorphableModelException::class,
        InvalidTerrafundFileUploadException::class,
        NoProgrammeFilesException::class,
        CsvContainsEmptyCellsException::class,
        CsvHasIncorrectHeadersException::class,
        NoTreeSpeciesProvided::class,
        NotEnoughFilesProvided::class,
        DuplicateOfferContactException::class,
        DuplicatePitchContactException::class,
        FinalOfferContactException::class,
        FinalPitchContactException::class,
        UsedTeamMemberException::class,
        SamePasswordException::class,
        InvalidJsonPatchException::class,
        InvalidOfferContactException::class,
        InvalidPitchContactException::class,
        HttpException::class,
        MonitoringExistsException::class,
        InvalidNegotiatorException::class,
        InvalidMonitoringException::class,
        OldTargetException::class,
        InvalidSubmitterException::class,
        InvalidTargetException::class,
        UserIsAlreadyPartOfProgrammeException::class,
        ProgrammeHasNoAimsException::class,
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
                    if (config('app.env') == 'production') {
                        App::make('sentry')->captureException($exception);
                    }
                    Log::error($exception);
                }

                return;
        }
    }

    public function render($request, Throwable $exception)
    {
        switch (get_class($exception)) {
            case AuthorizationException::class:
                return JsonResponseHelper::error([], 403);
            case FailedLoginException::class:
                return JsonResponseHelper::error([], 401);
            case NotFoundHttpException::class:
            case ModelNotFoundException::class:
                return JsonResponseHelper::error([], 404);
            case MethodNotAllowedHttpException::class:
                return JsonResponseHelper::error([], 405);
            case ValidationException::class:
                return JsonResponseHelper::error($exception->errors(), 422);
            case ThrottleRequestsException::class:
                return JsonResponseHelper::error([], 429);
            case MonitoringExistsException::class:
                $errors = ErrorHelper::create('*', 'project', 'CUSTOM', 'has monitoring');

                return JsonResponseHelper::error($errors, 422);
            case InvisiblePitchException::class:
                $errors = ErrorHelper::create('*', 'pitch', 'CUSTOM', 'has an invalid visibility');

                return JsonResponseHelper::error($errors, 422);
            case InvalidTargetException::class:
                $errors = ErrorHelper::create('*', 'target', 'CUSTOM', 'has an empty GeoJSON');

                return JsonResponseHelper::error($errors, 422);
            case InvisibleOfferException::class:
                $errors = ErrorHelper::create('*', 'offer', 'CUSTOM', 'has an invalid visibility');

                return JsonResponseHelper::error($errors, 422);
            case DuplicateInterestException::class:
                $errors = ErrorHelper::create('*', 'initiator', 'CUSTOM', 'has already shown interest');

                return JsonResponseHelper::error($errors, 422);
            case NoMatchingDraftsException::class:
                $errors = ErrorHelper::create('*', 'drafts', 'CUSTOM', 'has no in progress related drafts');

                return JsonResponseHelper::error($errors, 422);
            case MismatchingDraftTypeException::class:
                $errors = ErrorHelper::create('*', 'drafts', 'CUSTOM', 'have mismatching types');

                return JsonResponseHelper::error($errors, 422);
            case InvalidUploadTypeException::class:
                $errors = ErrorHelper::create('*', 'upload', 'CUSTOM', 'type is invalid');

                return JsonResponseHelper::error($errors, 422);
            case InviteAlreadyAcceptedException::class:
                $errors = ErrorHelper::create('*', 'monitoring invite', 'ALREADY_ACCEPTED', 'has already been accepted');

                return JsonResponseHelper::error($errors, 422);
            case DuplicateUploadException::class:
                $errors = ErrorHelper::create('*', 'upload', 'CUSTOM', 'is used multiple times');

                return JsonResponseHelper::error($errors, 422);
            case UploadNotFoundException::class:
                $errors = ErrorHelper::create('*', 'upload', 'CUSTOM', 'does not exist');

                return JsonResponseHelper::error($errors, 422);
            case ExternalAPIException::class:
                $errors = ErrorHelper::create('*', 'external API', 'EXTERNAL_API', 'has returned a server error');

                return JsonResponseHelper::error($errors, 500);
            case CorruptedUploadException::class:
                $errors = ErrorHelper::create('*', 'upload', 'CUSTOM', 'is corrupted');

                return JsonResponseHelper::error($errors, 422);
            case OldTargetException::class:
                $errors = ErrorHelper::create('*', 'target', 'CUSTOM', 'is not the latest target');

                return JsonResponseHelper::error($errors, 422);
            case InvalidSearchConditionsException::class:
                $errors = ErrorHelper::create('*', 'search conditions', 'CUSTOM', 'are invalid');

                return JsonResponseHelper::error($errors, 422);
            case InvalidFrameworkInviteCodeException::class:
                $errors = ErrorHelper::create('CODE', 'framework invite code', 'CUSTOM', 'is invalid');

                return JsonResponseHelper::error($errors, 422);
            case InvalidProgrammeFileUploadException::class:
                $errors = ErrorHelper::create('FILE', 'file upload', 'CUSTOM', 'is missing or failed');

                return JsonResponseHelper::error($errors, 422);
            case InvalidSiteFileUploadException::class:
                $errors = ErrorHelper::create('FILE', 'file upload', 'CUSTOM', 'is missing or failed');

                return JsonResponseHelper::error($errors, 422);
            case InvalidMorphableModelException::class:
                $errors = ErrorHelper::create('MORPHABLE_MODEL', 'attached entity', 'CUSTOM', 'is invalid');

                return JsonResponseHelper::error($errors, 422);
            case InvalidTerrafundFileUploadException::class:
                $errors = ErrorHelper::create('TERRAFUND_FILE', 'file upload', 'CUSTOM', 'is missing or failed');

                return JsonResponseHelper::error($errors, 422);
            case NoProgrammeFilesException::class:
                $errors = ErrorHelper::create('TERRAFUND_PROGRAMME_FILES', 'programme files', 'CUSTOM', 'are empty');

                return JsonResponseHelper::error($errors, 422);
            case CsvContainsEmptyCellsException::class:
                $errors = ErrorHelper::create('FILE', 'file upload', 'CUSTOM', 'contains empty cells');

                return JsonResponseHelper::error($errors, 422);
            case CsvHasIncorrectHeadersException::class:
                $errors = ErrorHelper::create('FILE', 'file upload', 'CUSTOM', 'has incorrect headings');

                return JsonResponseHelper::error($errors, 422);
            case NoTreeSpeciesProvided::class:
                $errors = ErrorHelper::create('TREE_SPECIES', 'tree species', 'CUSTOM', 'are missing');

                return JsonResponseHelper::error($errors, 422);
            case NotEnoughFilesProvided::class:
                $errors = ErrorHelper::create('PHOTOS_VIDEOS', 'photos and videos', 'NOT_ENOUGH_MEDIA', 'have an insufficient count');

                return JsonResponseHelper::error($errors, 422);
            case DuplicateOfferContactException::class:
                $errors = ErrorHelper::create('*', 'offer contact', 'CUSTOM', 'already exists');

                return JsonResponseHelper::error($errors, 422);
            case DuplicatePitchContactException::class:
                $errors = ErrorHelper::create('*', 'pitch contact', 'CUSTOM', 'already exists');

                return JsonResponseHelper::error($errors, 422);
            case InvalidSubmitterException::class:
                $errors = ErrorHelper::create('*', 'user', 'CUSTOM', 'cannot submit progress updates');

                return JsonResponseHelper::error($errors, 422);
            case FinalOfferContactException::class:
                $errors = ErrorHelper::create('*', 'contact', 'CUSTOM', 'is the final offer contact');

                return JsonResponseHelper::error($errors, 422);
            case FinalPitchContactException::class:
                $errors = ErrorHelper::create('*', 'contact', 'CUSTOM', 'is the final pitch contact');

                return JsonResponseHelper::error($errors, 422);
            case UsedTeamMemberException::class:
                $errors = ErrorHelper::create('*', 'team member', 'CUSTOM', 'is being used as a contact');

                return JsonResponseHelper::error($errors, 422);
            case SamePasswordException::class:
                $errors = ErrorHelper::create('password', 'new password', 'CUSTOM', 'must be different to the old password');

                return JsonResponseHelper::error($errors, 422);
            case InvalidJsonPatchException::class:
                $errors = ErrorHelper::create('*', 'patch operation', 'CUSTOM', 'is invalid');

                return JsonResponseHelper::error($errors, 422);
            case InvalidMonitoringException::class:
                $errors = ErrorHelper::create('*', 'monitoring', 'CUSTOM', 'has an invalid stage');

                return JsonResponseHelper::error($errors, 422);
            case InvalidOfferContactException::class:
                $errors = ErrorHelper::create('*', 'offer contact', 'CUSTOM', 'must contain exactly one contact');

                return JsonResponseHelper::error($errors, 422);
            case InvalidPitchContactException::class:
                $errors = ErrorHelper::create('*', 'pitch contact', 'CUSTOM', 'must contain exactly one contact');

                return JsonResponseHelper::error($errors, 422);
            case HttpException::class:
                return JsonResponseHelper::error([], $exception->getStatusCode());
            case InvalidNegotiatorException::class:
                $errors = ErrorHelper::create('*', 'negotiator', 'CUSTOM', 'is invalid');

                return JsonResponseHelper::error($errors, 422);
            case UserIsAlreadyPartOfProgrammeException::class:
                $errors = ErrorHelper::create('*', 'email_address', 'UNIQUE_PARTNER', 'is already a part of this programme');

                return JsonResponseHelper::error($errors, 422);
            case ProgrammeHasNoAimsException::class:
                return JsonResponseHelper::error([], 404);
            default:
                if (config('app.env') == 'local') {
                    return new Response($this->renderExceptionContent($exception), 500, ['Content-Type' => 'text/html']);
                } else {
                    return JsonResponseHelper::error([], 500);
                }
        }
    }
}
