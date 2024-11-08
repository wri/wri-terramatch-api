<?php

namespace App\Jobs\V2;

use App\Mail\ReportReminder as ReportReminderMail;
use App\Models\V2\EntityModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendReportReminderEmailsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private EntityModel $entity;

    private $feedback;

    public function __construct(EntityModel $entity, $feedback)
    {
        $this->entity = $entity;
        $this->feedback = $feedback;
    }

    public function handle(): void
    {

        $users = $this->entity->project->users;
        $skipRecipients = collect(explode(',', getenv('ENTITY_UPDATE_DO_NOT_EMAIL')));

        if (empty($users)) {
            return;
        }

        foreach ($users as $user) {
            if ($skipRecipients->contains($user->email_address)) {
                continue;
            }
            Mail::to($user->email_address)->send(new ReportReminderMail($this->entity, $this->feedback, $user));
        }
    }
}
