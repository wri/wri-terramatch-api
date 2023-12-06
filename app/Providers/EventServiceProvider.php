<?php

namespace App\Providers;

use App\Events\V2\Application\ApplicationSubmittedEvent;
use App\Events\V2\Form\FormSubmissionApprovedEvent;
use App\Events\V2\General\EntityStatusChangeEvent;
use App\Events\V2\Organisation\OrganisationApprovedEvent;
use App\Events\V2\Organisation\OrganisationRejectedEvent;
use App\Events\V2\Organisation\OrganisationSubmittedEvent;
use App\Events\V2\Organisation\OrganisationUserJoinRequestEvent;
use App\Events\V2\Organisation\OrganisationUserRequestApprovedEvent;
use App\Events\V2\Organisation\OrganisationUserRequestRejectedEvent;
use App\Listeners\v2\Application\ApplicationSubmittedConfirmationSendEmail;
use App\Listeners\v2\Files\CatchMediaConversionCompletionListener;
use App\Listeners\v2\Files\CatchMediaConversionStartListener;
use App\Listeners\v2\Form\FormSubmissionNextStage;
use App\Listeners\v2\Form\SetProjectPitchActive;
use App\Listeners\v2\General\StatusChangeAction;
use App\Listeners\v2\Organisation\OrganisationApprovedSendEmail;
use App\Listeners\v2\Organisation\OrganisationRejectedSendEmail;
use App\Listeners\v2\Organisation\OrganisationSubmittedConfirmationSendEmail;
use App\Listeners\v2\Organisation\OrganisationUserApprovedSendEmail;
use App\Listeners\v2\Organisation\OrganisationUserJoinRequestNotification;
use App\Listeners\v2\Organisation\OrganisationUserJoinRequestSendEmail;
use App\Listeners\v2\Organisation\OrganisationUserRejectedSendEmail;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Observers\Terrafund\TerrafundNurserySubmissionObserver;
use App\Observers\Terrafund\TerrafundProgrammeSubmissionObserver;
use App\Observers\Terrafund\TerrafundSiteSubmissionObserver;
use App\Observers\V2\FormObserver;
use App\Observers\V2\FormSubmissionObserver;
use App\Observers\V2\NurseryObserver;
use App\Observers\V2\ProjectObserver;
use App\Observers\V2\SiteObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompleted;
use Spatie\MediaLibrary\Conversions\Events\ConversionWillStart;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ApplicationSubmittedEvent::class => [
            ApplicationSubmittedConfirmationSendEmail::class,
            SetProjectPitchActive::class,
        ],

        EntityStatusChangeEvent::class => [StatusChangeAction::class],

        FormSubmissionApprovedEvent::class => [FormSubmissionNextStage::class],

        OrganisationSubmittedEvent::class => [OrganisationSubmittedConfirmationSendEmail::class],
        OrganisationApprovedEvent::class => [OrganisationApprovedSendEmail::class],
        OrganisationRejectedEvent::class => [OrganisationRejectedSendEmail::class],

        OrganisationUserJoinRequestEvent::class => [
            OrganisationUserJoinRequestNotification::class,
            OrganisationUserJoinRequestSendEmail::class,
        ],
        OrganisationUserRequestApprovedEvent::class => [OrganisationUserApprovedSendEmail::class],
        OrganisationUserRequestRejectedEvent::class => [OrganisationUserRejectedSendEmail::class],

        ConversionWillStart::class => [CatchMediaConversionStartListener::class],
        ConversionHasBeenCompleted::class => [CatchMediaConversionCompletionListener::class],
    ];

    /**
     * Register any events for your application.
     */
    public function boot()
    {
        parent::boot();
        TerrafundProgrammeSubmission::observe(TerrafundProgrammeSubmissionObserver::class);
        TerrafundNurserySubmission::observe(TerrafundNurserySubmissionObserver::class);
        TerrafundSiteSubmission::observe(TerrafundSiteSubmissionObserver::class);

        FormSubmission::observe(FormSubmissionObserver::class);
        Form::observe(FormObserver::class);

        Nursery::observe(NurseryObserver::class);
        Site::observe(SiteObserver::class);
        Project::observe(ProjectObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
