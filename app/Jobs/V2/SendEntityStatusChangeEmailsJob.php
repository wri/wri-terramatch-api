<?php

namespace App\Jobs\V2;

use App\Mail\EntityStatusChange as EntityStatusChangeMail;
use App\Models\Traits\skipRecipientsTrait;
use App\Models\V2\EntityModel;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEntityStatusChangeEmailsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use skipRecipientsTrait;

    private EntityModel $entity;

    public function __construct(EntityModel $entity)
    {
        $this->entity = $entity;
    }

    public function handle(): void
    {
        if ($this->entity->status != EntityStatusStateMachine::APPROVED &&
            $this->entity->status != EntityStatusStateMachine::NEEDS_MORE_INFORMATION &&
            $this->entity->update_request_status != EntityStatusStateMachine::NEEDS_MORE_INFORMATION) {
            return;
        }

        $usersFromProject = $this->entity->project->users;
        $usersFromProject = $this->skipRecipients($usersFromProject);
        if (empty($usersFromProject)) {
            return;
        }

        // TODO: This is a temporary hack to avoid spamming folks that have a funky role right now. In the future,
        // they will have a different role, and we can simply skip sending this email to anybody with that role.
        foreach ($usersFromProject as $user) {
            Mail::to($user['email_address'])->send(new EntityStatusChangeMail($this->entity, $user));
        }
    }
}
