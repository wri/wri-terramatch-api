<?php

namespace App\Console\Commands\Migration;

use App\Models\SiteSubmission;
use App\Models\SiteSubmissionDisturbance;
use App\Models\Terrafund\TerrafundDisturbance;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\V2\Disturbance;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;

class DisturbanceMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:disturbance {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Disturbance Data only to  V2 tables';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            Disturbance::truncate();
        }

        $collection = SiteSubmissionDisturbance::all();
        foreach ($collection as $disturbance) {
            $count++;
            $map = $this->mapSiteSubmissionDisturbanceValues($disturbance);
            if (is_array($map)) {
                $new = Disturbance::create($map);
                $created++;

                if ($this->option('timestamps')) {
                    $new->created_at = $disturbance->created_at;
                    $new->updated_at = $disturbance->updated_at;
                    $new->save();
                }
            }
        }

        $collection = TerrafundDisturbance::all();
        foreach ($collection as $disturbance) {
            $count++;
            $map = $this->mapTerrafundDisturbanceValues($disturbance);

            if (is_array($map)) {
                $new = Disturbance::create($map);
                $created++;

                if ($this->option('timestamps')) {
                    $new->created_at = $disturbance->created_at;
                    $new->updated_at = $disturbance->updated_at;
                    $new->save();
                }
            }
        }

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }

    private function mapSiteSubmissionDisturbanceValues(SiteSubmissionDisturbance $disturbance): ?array
    {
        $report = SiteReport::where('old_model', SiteSubmission::class)
            ->where('old_id', $disturbance->site_submission_id)
            ->first();

        if (! empty($report)) {
            return [
                'old_model' => SiteSubmissionDisturbance::class,
                'old_id' => $disturbance->id,

                'type' => data_get($disturbance, 'disturbance_type'),
                'intensity' => data_get($disturbance, 'intensity'),
                'description' => data_get($disturbance, 'description'),
                'extent' => data_get($disturbance, 'extent'),
                'disturbanceable_id' => $report->id,
                'disturbanceable_type' => SiteReport::class,
            ];
        }

        return null;
    }

    private function mapTerrafundDisturbanceValues(TerrafundDisturbance $disturbance): ?array
    {
        $report = SiteReport::where('old_model', TerrafundSiteSubmission::class)
            ->where('old_id', $disturbance->disturbanceable_id)
            ->first();

        if (! empty($report)) {
            return [
                'old_model' => TerrafundDisturbance::class,
                'old_id' => $disturbance->id,

                'type' => data_get($disturbance, 'type'),
                'description' => data_get($disturbance, 'description'),
                'disturbanceable_id' => $report->id,
                'disturbanceable_type' => SiteReport::class,
            ];
        }

        return null;
    }
}
