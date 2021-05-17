<?php

namespace Tests\Feature;


use App\Jobs\NotifyUpdateVisibilityJob;
use App\Models\ElevatorVideo as ElevatorVideoModel;
use App\Models\FilterRecord as FilterRecordModel;
use App\Models\Interest as InterestModel;
use App\Models\Match as MatchModel;
use App\Models\Notification as NotificationModel;
use App\Models\NotificationsBuffer as NotificationsBufferModel;
use App\Models\PasswordReset as PasswordResetModel;
use App\Models\Upload as UploadModel;
use App\Models\Verification as VerificationModel;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CommandsTest extends TestCase
{
    public function testCreateVisibilityNotificationCommand()
    {
        Queue::fake();

        $past = new \DateTime("now - 3 days - 4 minutes", new \DateTimeZone("UTC"));
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

    public function testFindMatchesCommand()
    {
        $presentDate = new \DateTime("now", new \DateTimeZone("UTC"));

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

        $this->assertDatabaseHas('interests', ['matched' => true]);
        $this->assertDatabaseHas('matches', ['primary_interest_id' => 3]);

    }

    public function testRemoveElevatorCommand()
    {
        $pastDate = new \DateTime("now - 2 day", new \DateTimeZone("UTC"));
        $elevatorVideo = ElevatorVideoModel::create([
            'user_id' => 2,
            'status' => 'errored',
            'created_at' => $pastDate,
            'updated_at' => $pastDate,
            'upload_id' => 1
        ]);
        $elevatorVideo->save();

        $this->artisan('remove-elevator-videos')
            ->assertExitCode(0);

        $this->assertDeleted('elevator_videos', ['id' => $elevatorVideo->id]);
    }

    public function testRemoveFilterRecordsCommand()
    {
        $pastDate = new \DateTime("now - 29 days", new \DateTimeZone("UTC"));
        $filterRecord = FilterRecordModel::create([
            'user_id' => 2,
            'organisation_id' => 1,
            'type' => 'offers',
            'created_at' => $pastDate,
            'updated_at' => $pastDate
        ]);
        $filterRecord->save();

        $this->artisan('remove-filter-records')
            ->assertExitCode(0);

        $this->assertDeleted('filter_records', ['user_id' => 2]);
    }

    public function testRemoveNotificationsBuffersCommand()
    {
        $pastDate = new \DateTime("now - 6 minutes", new \DateTimeZone("UTC"));
        $notificationBuffer = NotificationsBufferModel::create([
            'identifier' => 'test_buffer_identifier',
            'created_at' => $pastDate,
            'updated_at' => $pastDate
        ]);
        $notificationBuffer->save();

        $this->artisan('remove-notifications-buffers')
            ->assertExitCode(0);

        $this->assertDeleted('notifications_buffer', ['identifier' => 'test_buffer_identifier']);
    }


    public function testRemoveNotificationsCommand()
    {
        $pastDate = new \DateTime("now - 91 days", new \DateTimeZone("UTC"));
        $notification = NotificationModel::create([
            'user_id' => 2,
            'title' => 'test_notification',
            'body' => 'Lorem ipsum ',
            'unread' => 0,
            'created_at' => $pastDate,
            'updated_at' => $pastDate
        ]);
        $notification->save();

        $this->artisan('remove-notifications')
            ->assertExitCode(0);

        $this->assertDeleted('notifications', ['title' => 'test_notification']);
    }


    public function testRemovePasswordResetsCommand()
    {
        $pastDate = new \DateTime("now - 4 hours", new \DateTimeZone("UTC"));
        $passwordReset = PasswordResetModel::create([
            'token' => 'token123',
            'user_id' => 2,
            'created_at' => $pastDate,
            'updated_at' => $pastDate
        ]);
        $passwordReset->save();

        $this->artisan('remove-password-resets')
            ->assertExitCode(0);

        $this->assertDeleted('password_resets', ['token' => 'token123']);
    }


    public function testRemoveUploadsCommand()
    {
        $past = new \DateTime("now - 1 day", new \DateTimeZone("UTC"));
        $uploads = UploadModel::create([
            'user_id' => 2,
            'location' => 'http://127.0.0.1:9000/wri/UT/Uu/test.mp4',
            'created_at' => $past,
            'updated_at' => $past
        ]);
        $uploads->save();

        $elevatorVideo = ElevatorVideoModel::create([
            'user_id' => 2,
            'status' => 'finished',
            'created_at' => $past,
            'updated_at' => $past,
            'upload_id' => $uploads->id
        ]);
        $elevatorVideo->save();

        $this->artisan('remove-uploads')
            ->assertExitCode(0);

        $this->assertDeleted('uploads', ['id' => $uploads->id]);
        $this->assertDeleted('elevator_videos', ['id' => $elevatorVideo->id]);

    }

    public function testRemoveVerificationsCommand()
    {
        $pastDate = new \DateTime("now - 49 hours", new \DateTimeZone("UTC"));
        $verification = VerificationModel::create([
            'token' => 'token123',
            'user_id' => 2,
            'created_at' => $pastDate,
            'updated_at' => $pastDate
        ]);
        $verification->save();

        $this->artisan('remove-verifications')
            ->assertExitCode(0);

        $this->assertDeleted('verifications', ['token' => 'token123']);
    }

    public function testRemoveElevatorVideosCommand()
    {
        $this->artisan('remove-elevator-videos')
            ->assertExitCode(0);
    }

    public function testCreateVisibilityNotificationsCommand()
    {
        $this->artisan('create-visibility-notifications')
            ->assertExitCode(0);
    }

    public function testSendUpcomingProgressUpdateNotifications()
    {
        $this->artisan('send-upcoming-progress-update-notifications')
            ->assertExitCode(0);
    }
}
