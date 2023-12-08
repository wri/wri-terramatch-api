<?php

namespace Tests\Legacy\Feature;

use App\Jobs\NotifyUpdateVisibilityJob;
use App\Mail\TerrafundReportReminder;
use App\Mail\TerrafundSiteAndNurseryReminder;
use App\Models\Draft;
use App\Models\ElevatorVideo as ElevatorVideoModel;
use App\Models\FilterRecord as FilterRecordModel;
use App\Models\Interest as InterestModel;
use App\Models\Matched as MatchModel;
use App\Models\Notification as NotificationModel;
use App\Models\NotificationsBuffer as NotificationsBufferModel;
use App\Models\Organisation;
use App\Models\PasswordReset as PasswordResetModel;
use App\Models\Programme;
use App\Models\Site;
use App\Models\SiteSubmission;
use App\Models\Submission;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\Upload as UploadModel;
use App\Models\User;
use App\Models\Verification as VerificationModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Legacy\LegacyTestCase;

final class CommandsTest extends LegacyTestCase
{
    public function testCreateVisibilityNotificationCommand(): void
    {
        Queue::fake();

        $past = new \DateTime('now - 3 days - 4 minutes', new \DateTimeZone('UTC'));
        $match = MatchModel::create([
            'primary_interest_id' => 2,
            'secondary_interest_id' => 1,
            'created_at' => $past,
            'updated_at' => $past,
        ]);

        $match->save();

        $this->artisan('create-visibility-notifications')
            ->assertExitCode(0);

        Queue::assertPushed(NotifyUpdateVisibilityJob::class);
    }

    public function testFindMatchesCommand(): void
    {
        $presentDate = new \DateTime('now', new \DateTimeZone('UTC'));

        $interest = InterestModel::create([
            'organisation_id' => 2,
            'initiator' => 'offer',
            'offer_id' => 3,
            'pitch_id' => 1,
            'created_at' => $presentDate,
            'updated_at' => $presentDate,
        ]);
        $interest->save();
        $interest = InterestModel::create([
            'organisation_id' => 1,
            'initiator' => 2,
            'offer_id' => 3,
            'pitch_id' => 1,
            'created_at' => $presentDate,
            'updated_at' => $presentDate,
        ]);
        $interest->save();

        $this->artisan('find-matches')
            ->assertExitCode(0);

        $this->assertDatabaseHas('interests', ['has_matched' => true]);
        $this->assertDatabaseHas('matches', ['primary_interest_id' => 3]);
    }

    public function testRemoveElevatorCommand(): void
    {
        $pastDate = new \DateTime('now - 2 day', new \DateTimeZone('UTC'));
        $elevatorVideo = ElevatorVideoModel::create([
            'user_id' => 2,
            'status' => 'errored',
            'created_at' => $pastDate,
            'updated_at' => $pastDate,
            'upload_id' => 1,
        ]);
        $elevatorVideo->save();

        $this->artisan('remove-elevator-videos')
            ->assertExitCode(0);

        $this->assertModelMissing($elevatorVideo);
    }

    public function testRemoveFilterRecordsCommand(): void
    {
        $pastDate = new \DateTime('now - 29 days', new \DateTimeZone('UTC'));
        $filterRecord = FilterRecordModel::create([
            'user_id' => 2,
            'organisation_id' => 1,
            'type' => 'offers',
            'created_at' => $pastDate,
            'updated_at' => $pastDate,
        ]);
        $filterRecord->save();

        $this->artisan('remove-filter-records')
            ->assertExitCode(0);

        $this->assertModelMissing($filterRecord);
    }

    public function testRemoveNotificationsBuffersCommand(): void
    {
        $pastDate = new \DateTime('now - 6 minutes', new \DateTimeZone('UTC'));
        $notificationBuffer = NotificationsBufferModel::create([
            'identifier' => 'test_buffer_identifier',
            'created_at' => $pastDate,
            'updated_at' => $pastDate,
        ]);
        $notificationBuffer->save();

        $this->artisan('remove-notifications-buffers')
            ->assertExitCode(0);

        $this->assertModelMissing($notificationBuffer);
    }

    public function testRemoveNotificationsCommand(): void
    {
        $pastDate = new \DateTime('now - 91 days', new \DateTimeZone('UTC'));
        $notification = NotificationModel::create([
            'user_id' => 2,
            'title' => 'test_notification',
            'body' => 'Lorem ipsum ',
            'unread' => 0,
            'created_at' => $pastDate,
            'updated_at' => $pastDate,
        ]);
        $notification->save();

        $this->artisan('remove-notifications')
            ->assertExitCode(0);

        $this->assertModelMissing($notification);
    }

    public function testRemovePasswordResetsCommand(): void
    {
        $pastDate = new \DateTime('now - 4 hours', new \DateTimeZone('UTC'));
        $passwordReset = PasswordResetModel::create([
            'token' => 'token123',
            'user_id' => 2,
            'created_at' => $pastDate,
            'updated_at' => $pastDate,
        ]);
        $passwordReset->save();

        $this->artisan('remove-password-resets')
            ->assertExitCode(0);

        $this->assertModelMissing($passwordReset);
    }

    public function testRemoveUploadsCommand(): void
    {
        $past = new \DateTime('now - 1 day', new \DateTimeZone('UTC'));
        $uploads = UploadModel::create([
            'user_id' => 2,
            'location' => 'http://127.0.0.1:9000/wri/UT/Uu/test.mp4',
            'created_at' => $past,
            'updated_at' => $past,
        ]);
        $uploads->save();

        $elevatorVideo = ElevatorVideoModel::create([
            'user_id' => 2,
            'status' => 'finished',
            'created_at' => $past,
            'updated_at' => $past,
            'upload_id' => $uploads->id,
        ]);
        $elevatorVideo->save();

        $this->artisan('remove-uploads')
            ->assertExitCode(0);

        $this->assertModelMissing($uploads);
        $this->assertModelMissing($elevatorVideo);
    }

    public function testRemoveVerificationsCommand(): void
    {
        $pastDate = new \DateTime('now - 49 hours', new \DateTimeZone('UTC'));
        $verification = VerificationModel::create([
            'token' => 'token123',
            'user_id' => 2,
            'created_at' => $pastDate,
            'updated_at' => $pastDate,
        ]);
        $verification->save();

        $this->artisan('remove-verifications')
            ->assertExitCode(0);

        $this->assertModelMissing($verification);
    }

    public function testRemoveOldExportFiles(): void
    {
        $this->artisan('remove-export-files')
            ->assertExitCode(0);
    }

    public function testRemoveElevatorVideosCommand(): void
    {
        $this->artisan('remove-elevator-videos')
            ->assertExitCode(0);
    }

    public function testCreateVisibilityNotificationsCommand(): void
    {
        $this->artisan('create-visibility-notifications')
            ->assertExitCode(0);
    }

    public function testSendUpcomingProgressUpdateNotifications(): void
    {
        $this->artisan('send-upcoming-progress-update-notifications')
            ->assertExitCode(0);
    }

    public function testAddTestDataForSiteSubmissions(): void
    {
        $this->artisan('send-upcoming-progress-update-notifications')
            ->assertExitCode(0);
    }

    public function testGenerateDueSubmissions(): void
    {
        $this->artisan('generate-control-site-due-submissions')
            ->assertExitCode(0);
    }

    public function testImportProgrammeCsv(): void
    {
        $this->artisan('import-programme-csv blank.csv')
            ->assertExitCode(0);
    }

    public function testImportSiteCsv(): void
    {
        $this->artisan('import-site-csv blank.csv')
            ->assertExitCode(0);
    }

    public function testImportSiteSubmissionCsv(): void
    {
        $this->artisan('import-site-submission-csv blank.csv')
            ->assertExitCode(0);
    }

    public function testImportProgrammeSubmissionCsv(): void
    {
        $this->artisan('import-programme-submission-csv blank.csv')
            ->assertExitCode(0);
    }

    public function testSendTerrafundSiteAndNurseryRemindersCommand(): void
    {
        Mail::fake();

        $programme = TerrafundProgramme::factory()->create([
            'name' => 'Command test',
        ]);
        $user = User::factory()->create([
            'organisation_id' => $programme->organisation_id,
        ]);
        $user->terrafundProgrammes()->attach($programme);

        $this->artisan('send-terrafund-site-and-nursery-reminders')
            ->assertExitCode(0);

        Mail::assertQueued(TerrafundSiteAndNurseryReminder::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email_address);
        });
    }

    public function testSendTerrafundReportRemindersCommand(): void
    {
        Mail::fake();

        $programme = TerrafundProgramme::factory()->create([
            'name' => 'Command test',
        ]);
        TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $programme->id,
        ]);
        $user = User::factory()->create([
            'organisation_id' => $programme->organisation_id,
        ]);
        $user->terrafundProgrammes()->attach($programme);

        $this->artisan('send-terrafund-report-reminders')
            ->assertExitCode(0);

        Mail::assertQueued(TerrafundReportReminder::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email_address);
        });
    }

    public function testUpdateDraftBlueprintsCommand(): void
    {
        /**
         * Note: if this first assertion has failed,
         * check that you've also updated the draft seeding
         * to reflect your new or removed blueprint properties and structure.
         */
        $this->artisan('update-draft-blueprints')
            ->assertExitCode(0);

        foreach (Draft::all() as $draft) {
            $id = $draft->id;
            $jsonData = $draft->data;
            $newData = json_decode($jsonData);
            $newData->testNode = 'test';
            $draft->data = json_encode($newData);
            $draft->save();

            $this->artisan('update-draft-blueprints')
                ->assertExitCode(1);

            $updated = Draft::find($id);
            $this->assertEquals($jsonData, $updated->data);
        }
    }

    public function testDeleteProgrammeCommand(): void
    {
        $programme = Programme::factory()->create();

        $this->artisan('delete-programme ' . $programme->id)
            ->assertExitCode(0);

        $programme->refresh();
        $this->assertNotNull($programme->deleted_at);
    }

    public function testDeleteProgrammeSubmissionCommand(): void
    {
        $submission = Submission::factory()->create();

        $this->artisan('delete-programme-submission ' . $submission->id)
            ->assertExitCode(0);

        $submission->refresh();
        $this->assertNotNull($submission->deleted_at);
    }

    public function testDeleteSiteCommand(): void
    {
        $site = Site::factory()->create();

        $this->artisan('delete-site ' . $site->id)
            ->assertExitCode(0);

        $site->refresh();
        $this->assertNotNull($site->deleted_at);
    }

    public function testDeleteSiteSubmissionCommand(): void
    {
        $submission = SiteSubmission::factory()->create();

        $this->artisan('delete-site-submission ' . $submission->id)
            ->assertExitCode(0);

        $submission->refresh();
        $this->assertNotNull($submission->deleted_at);
    }

    public function testDeleteTerrafundProgrammeCommand(): void
    {
        $programme = TerrafundProgramme::factory()->create();

        $this->artisan('delete-terrafund-programme ' . $programme->id)
            ->assertExitCode(0);

        $programme->refresh();
        $this->assertNotNull($programme->deleted_at);
    }

    public function testDeleteTerrafundProgrammeSubmissionCommand(): void
    {
        $submission = TerrafundProgrammeSubmission::factory()->create();

        $this->artisan('delete-terrafund-programme-submission ' . $submission->id)
            ->assertExitCode(0);

        $submission->refresh();
        $this->assertNotNull($submission->deleted_at);
    }

    public function testDeleteTerrafundSiteCommand(): void
    {
        $site = TerrafundSite::factory()->create();

        $this->artisan('delete-terrafund-site ' . $site->id)
            ->assertExitCode(0);

        $site->refresh();
        $this->assertNotNull($site->deleted_at);
    }

    public function testDeleteTerrafundSiteSubmissionCommand(): void
    {
        $submission = TerrafundSiteSubmission::factory()->create();

        $this->artisan('delete-terrafund-site-submission ' . $submission->id)
            ->assertExitCode(0);

        $submission->refresh();
        $this->assertNotNull($submission->deleted_at);
    }

    public function testDeleteTerrafundNurseryCommand(): void
    {
        $nursery = TerrafundNursery::factory()->create();

        $this->artisan('delete-terrafund-nursery ' . $nursery->id)
            ->assertExitCode(0);

        $nursery->refresh();
        $this->assertNotNull($nursery->deleted_at);
    }

    public function testDeleteTerrafundNurserySubmissionCommand(): void
    {
        $submission = TerrafundNurserySubmission::factory()->create();

        $this->artisan('delete-terrafund-nursery-submission ' . $submission->id)
            ->assertExitCode(0);

        $submission->refresh();
        $this->assertNotNull($submission->deleted_at);
    }

    public function testResetUserPasswordCommand(): void
    {
        $user = User::factory()->create();

        $this->artisan("reset-user-password $user->id newtestpassword")
            ->assertExitCode(0);

        $user->refresh();
        $this->assertTrue(Hash::check('newtestpassword', $user->password));
    }

    public function testVerifyUserCommand(): void
    {
        $user = User::factory()->create([
            'email_address_verified_at' => null,
        ]);

        $this->assertNull($user->email_address_verified_at);

        $this->artisan("verify-user $user->id")
            ->assertExitCode(0);

        $user->refresh();
        $this->assertNotNull($user->email_address_verified_at);
    }

    public function testV2MigrateOrganisationsCommand(): void
    {
        Organisation::factory()->count(3)->create();

        $organisation = Organisation::first();
        $this->assertNull($organisation->name);

        $this->artisan('v2-migrate-organisations')
            ->assertExitCode(0);

        $organisation->refresh();

        $this->assertNotNull($organisation->uuid);
        $this->assertNotNull($organisation->name);
    }

    public function testV2MigratePendingOrganisationsCommand()
    {
        Organisation::factory()->count(3)->create();

        $this->artisan('v2-migrate-pending-organisations')
            ->assertExitCode(0);
    }

    public function testV2MigrateUsersCommand()
    {
        $user = User::factory()->create();

        $user->uuid = null;
        $user->save();
        $this->assertNull($user->uuid);

        $this->artisan('v2-migrate-users')
            ->assertExitCode(0);

        $user->refresh();

        $this->assertNotNull($user->uuid);
    }

    public function testV2CustomFormUpdateDataCommand(): void
    {
        $this->artisan('v2-custom-form-update-data')
            ->assertExitCode(0);
    }

    public function testV2CustomFormPrepPhase2Command(): void
    {
        $this->artisan('v2-custom-form-prep-phase2')
            ->assertExitCode(0);
    }

    public function testV2GenerateTranslationFileCommand(): void
    {
        $this->artisan('v2-translation-file-generate --testing')
            ->assertExitCode(0);
    }

    /** @group slow */
    public function testV2ImportTranslationFileCommand()
    {
        $this->artisan('v2-translation-file-import --testing')
            ->assertExitCode(0);
    }

    public function testV2CustomFormRFPUpdateDataCommand()
    {
        $this->artisan('v2-custom-form-rfp-update-data')
            ->assertExitCode(0);
    }
}
