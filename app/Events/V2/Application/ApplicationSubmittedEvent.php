<?php

namespace App\Events\V2\Application;

use App\Models\User;
use App\Models\V2\Forms\FormSubmission;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmittedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public User $user;

    public FormSubmission $formSubmission;

    public function __construct(User $user, FormSubmission $formSubmission)
    {
        $this->user = $user;
        $this->formSubmission = $formSubmission;
    }
}
