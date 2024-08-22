<?php

namespace App\Events\V2\Form;

use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FormSubmissionApprovedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public User $user;

    public FormSubmission $formSubmission;

    public function __construct(FormSubmission $formSubmission)
    {
        $this->formSubmission = $formSubmission;
    }
}
