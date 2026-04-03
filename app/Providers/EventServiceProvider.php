<?php

namespace App\Providers;

use App\Events\V2\General\EntityDeleteEvent;
use App\Events\V2\General\EntityStatusChangeEvent;
use App\Listeners\v2\General\DeleteAction;
use App\Listeners\v2\General\StatusChangeAction;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Observers\V2\FormObserver;
use App\Observers\V2\FormSubmissionObserver;
use App\Observers\V2\NurseryObserver;
use App\Observers\V2\ProjectObserver;
use App\Observers\V2\SiteObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        EntityStatusChangeEvent::class => [StatusChangeAction::class],
        EntityDeleteEvent::class => [DeleteAction::class],
    ];

    /**
     * Register any events for your application.
     */
    public function boot()
    {
        parent::boot();
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
