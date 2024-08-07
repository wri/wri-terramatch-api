<?php

namespace Tests\Unit\Jobs\V2;

use App\Jobs\V2\SendReportRemindersJob;
use App\Mail\TerrafundReportReminder;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendReportRemindersJobTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    public function test_emails_get_queued_for_users_with_terrafund_projects()
    {
        $organisation = Organisation::factory()->create();

        $users = User::factory()
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

        SendReportRemindersJob::dispatchSync('terrafund');

        $emailBody = 'Your next report is due on July 31. It should reflect any progress made between January 1, 2023 and June 30, 2022.<br><br>' .
            'If you have any questions, feel free to message us at info@terramatch.org.' .
            '<br><br>---<br><br>' .
            'Votre prochain rapport doit être remis le 31 juillet. Il doit refléter tous les progrès réalisés entre le 1er janvier 2023 et le 30 juin 2023. ';

        Mail::assertQueued(TerrafundReportReminder::class, function (TerrafundReportReminder $terrafundReportReminder) use ($users, $emailBody) {
            return $terrafundReportReminder->hasTo($users->pluck('email_address')) &&
                $terrafundReportReminder->subject('Terrafund Report Reminder') &&
                $terrafundReportReminder->body = $emailBody &&
                $terrafundReportReminder->cta = 'View Project' &&
                $terrafundReportReminder->transactional = true;
        });
    }

    public function test_emails_do_not_get_queued_for_users_with_ppc_projects()
    {
        $organisation = Organisation::factory()->create();

        User::factory()
            ->admin()
            ->has(Project::factory()
                ->has(
                    Site::factory()
                        ->ppc()
                )
                ->has(
                    Nursery::factory()
                        ->ppc()
                )
                ->ppc())
            ->count(5)
            ->create(['organisation_id' => $organisation->id]);

        SendReportRemindersJob::dispatchSync('terrafund');

        Mail::assertNotQueued(TerrafundReportReminder::class);
    }
}
