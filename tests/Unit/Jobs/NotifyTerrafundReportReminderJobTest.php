<?php

namespace Tests\Unit\Jobs;

use App\Jobs\NotifyTerrafundReportReminderJob;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NotifyTerrafundReportReminderJobTest extends TestCase
{
    use RefreshDatabase;

    public function testJobCreatesNotifications(): void
    {
        $user = User::factory()->create();
        $programme = TerrafundProgramme::factory()->create();
        $user->terrafundProgrammes()->attach($programme);
        NotifyTerrafundReportReminderJob::dispatchSync($user, $programme);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'action' => 'terrafund_report_reminder',
            'referenced_model' => 'TerrafundProgramme',
            'referenced_model_id' => $programme->id,
            'hidden_from_app' => 1,
        ]);
    }
}
