<?php

namespace App\Console\Commands;

use App\Models\V2\Sites\SitePolygon;
use App\Services\RunIndicatorAnalysisService;
use Illuminate\Console\Command;

class RunIndicatorAnalysisCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run-indicator-analysis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run indicator analysis';

    protected RunIndicatorAnalysisService $service;

    public function __construct(RunIndicatorAnalysisService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle(): int
    {
        $this->info('Running indicator analysis');

        $slugs = [
            'restorationByLandUse',
            'restorationByStrategy',
        ];

        $polygonsUuids = SitePolygon::where('is_active', true)->where('status', 'approved')->pluck('poly_id')->toArray();
        $request = [
            'uuids' => $polygonsUuids,
        ];

        foreach ($slugs as $slug) {
            $this->info('Analysis started for slug: ' . $slug);
            $response = $this->service->run($request, $slug);
            $this->info('Analysis finished for slug: ' . $slug);
        }

        return 0;
    }
}
