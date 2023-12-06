<?php

namespace App\Jobs;

use App\Models\FilterRecord as FilterRecordModel;
use App\Models\User as UserModel;
use App\Services\Search\Conditions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateFilterRecordJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $user;

    private $type;

    private $conditions;

    public function __construct(UserModel $user, String $type, Conditions $conditions)
    {
        $this->user = $user;
        $this->type = $type;
        $this->conditions = $conditions;
    }

    public function handle()
    {
        if (count($this->conditions->where) == 0) {
            return;
        }
        $filterRecord = new FilterRecordModel();
        foreach ($this->conditions->where as $where) {
            $key = $where[0];
            $filterRecord->$key = true;
        }
        $filterRecord->organisation_id = $this->user->organisation_id;
        $filterRecord->user_id = $this->user->id;
        $filterRecord->type = $this->type;
        $filterRecord->saveOrFail();
    }
}
