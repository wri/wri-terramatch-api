<?php

namespace App\Jobs;

use App\Models\FilterRecord;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RecordFilters implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var array
     */
    private $data;

    /**
     * RecordFilters constructor.
     * @param User   $user
     * @param string $type
     * @param array  $filters
     */
    public function __construct(User $user, string $type, array $filters = null)
    {
        $this->user = $user;
        $this->type = $type;
        $this->filters = $filters;
        $this->data = [
            'user_id' => $this->user->id,
            'organisation_id' => $this->user->organisation_id,
            'type' => $this->type,
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!is_array($this->filters) && count($this->filters) < 1)
        {
            return;
        }

        $filterRecord = new FilterRecord();

        $data = collect($this->filters)
            ->map(function($item) {
                return $item['attribute'];
            })->filter(function ($item) use ($filterRecord) {
                return in_array($item, $filterRecord->fillable);
            })->mapWithKeys(function ($item) {
                return [ $item => true ];
            })->merge($this->data)->toArray();

        $filterRecord
            ->fill($data)
            ->saveOrFail();
    }
}
