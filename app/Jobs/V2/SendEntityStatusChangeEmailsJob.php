<?php

namespace App\Jobs\V2;

use App\Mail\EntityStatusChange as EntityStatusChangeMail;
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

        $emailAddresses = $this->entity->project->users()->pluck('email_address');
        if (empty($emailAddresses)) {
            return;
        }

        foreach ($emailAddresses as $emailAddress) {
            Mail::to($emailAddress)->send(new EntityStatusChangeMail($this->entity));
        }
    }
}