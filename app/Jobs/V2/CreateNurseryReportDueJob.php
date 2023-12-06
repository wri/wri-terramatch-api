<?php

namespace App\Jobs\V2;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateNurseryReportDueJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private ?Carbon $dueDate;

    private $frameworkKey;

    public function __construct(string $frameworkKey, int $dueMonth = null)
    {
        $this->frameworkKey = $frameworkKey;

        if ($dueMonth) {
            $carbonDate = Carbon::createFromFormat('m', $dueMonth);
            $this->dueDate = $carbonDate->isPast() ? $carbonDate->addYear()->firstOfMonth(5) : $carbonDate->firstOfMonth(5);
        } else {
            $this->dueDate = Carbon::now()->addMonth()->startOfDay();
        }
    }

    public function handle()
    {
        Nursery::where('framework_key', $this->frameworkKey)
            ->chunkById(100, function ($nurseries) {
                foreach ($nurseries as $nursery) {
                    $report = NurseryReport::create([
                        'framework_key' => $this->frameworkKey,
                        'nursery_id' => $nursery->id,
                        'status' => NurseryReport::STATUS_DUE,
                        'due_at' => $this->dueDate,
                    ]);
                }
            });
    }
}
