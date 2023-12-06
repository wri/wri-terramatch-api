<?php

namespace App\Jobs\V2;

use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateSiteReportDueJob implements ShouldQueue
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
        Site::where('framework_key', $this->frameworkKey)
            ->chunkById(100, function ($sites) {
                foreach ($sites as $site) {
                    $report = SiteReport::create([
                        'framework_key' => $this->frameworkKey,
                        'site_id' => $site->id,
                        'status' => SiteReport::STATUS_DUE,
                        'due_at' => $this->dueDate,
                    ]);
                }
            });
    }
}
