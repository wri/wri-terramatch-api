<?php

namespace App\Jobs\V2;

use App\Mail\ProjectManager as ProjectManagerMail;
use App\Models\V2\EntityModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTaskDigestProjectManagerJob implements ShouldQueue
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
        $usersManagers = $this->entity->project->managers ?? $this->entity->managers;

        if (empty($usersManagers)) {
            return;
        }

        foreach ($usersManagers as $manager) {
            Mail::to($manager['email_address'])->send(new ProjectManagerMail($this->entity, $manager));
        }
    }
}
