<?php

namespace Tests\Unit\Jobs\V2;

use App\Jobs\V2\SendSiteAndNurseryRemindersJob;
use App\Mail\TerrafundSiteAndNurseryReminder;
use App\Models\User;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendSiteAndNurseryRemindersJobTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    public function test_emails_get_queued_for_users_with_terrafund_projects_that_have_no_sites_or_nurseries()
    {
        $organisation = Organisation::factory()->create();

        $users = User::factory()
            ->terrafundAdmin()
            ->has(Project::factory()->terrafund())
            ->count(5)
            ->create(['organisation_id' => $organisation->id]);

        SendSiteAndNurseryRemindersJob::dispatchSync('terrafund');

        $emailBody = 'You haven\'t created any sites or nurseries for your project, reports are due in a month.<br><br>' .
            'Click below to create.<br><br>';

        Mail::assertQueued(TerrafundSiteAndNurseryReminder::class, function (TerrafundSiteAndNurseryReminder $terrafundSiteAndNurseryReminder) use ($users, $emailBody) {
            return $terrafundSiteAndNurseryReminder->hasTo($users->pluck('email_address')) &&
                $terrafundSiteAndNurseryReminder->subject('Terrafund Site & Nursery Reminder') &&
                $terrafundSiteAndNurseryReminder->body = $emailBody &&
                $terrafundSiteAndNurseryReminder->cta = 'Create a site or nursery' &&
                $terrafundSiteAndNurseryReminder->transactional = true;
        });
    }

    public function test_emails_do_not_get_queued_for_users_with_terrafund_projects_that_have_sites_or_nurseries()
    {
        $organisation = Organisation::factory()->create();

        User::factory()
            ->terrafundAdmin()
            ->has(Project::factory()
                ->has(
                    Site::factory()
                        ->terrafund()
                )
                ->has(
                    Nursery::factory()
                        ->terrafund()
                )
                ->terrafund())
            ->count(5)
            ->create(['organisation_id' => $organisation->id]);

        SendSiteAndNurseryRemindersJob::dispatchSync('terrafund');

        Mail::assertNotQueued(TerrafundSiteAndNurseryReminder::class);
    }

    public function test_emails_do_not_get_queued_for_users_with_ppc_projects_that_have_no_sites_or_nurseries()
    {
        $organisation = Organisation::factory()->create();

        User::factory()
            ->terrafundAdmin()
            ->has(Project::factory()->ppc())
            ->count(5)
            ->create(['organisation_id' => $organisation->id]);

        SendSiteAndNurseryRemindersJob::dispatchSync('terrafund');

        Mail::assertNotQueued(TerrafundSiteAndNurseryReminder::class);
    }
}
