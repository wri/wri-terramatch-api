<?php

namespace App\Console\Commands\Migration;

use App\Models\DirectSeeding;
use App\Models\SeedDetail;
use App\Models\Site as PPCSite;
use App\Models\SiteSubmission;
use App\Models\V2\Seeding;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;

class SeedingMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:seeding {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Seeding Data only to  V2 tables';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            Seeding::truncate();
        }

        $collection = SeedDetail::all();
        foreach ($collection as $seed) {
            $count++;
            $map = $this->mapSeedDetailValues($seed);
            if (! empty($map)) {
                $new = Seeding::create($map);
                $created++;

                if ($this->option('timestamps')) {
                    $new->created_at = $seed->created_at;
                    $new->updated_at = $seed->updated_at;
                    $new->save();
                }
            }
        }

        $collection = DirectSeeding::all();
        foreach ($collection as $seed) {
            $count++;
            $map = $this->mapDirectSeedingValues($seed);
            if (! empty($map)) {
                $new = Seeding::create($map);
                $created++;

                if ($this->option('timestamps')) {
                    $new->created_at = $seed->created_at;
                    $new->updated_at = $seed->updated_at;
                    $new->save();
                }
            }
        }

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }

    private function mapSeedDetailValues(SeedDetail $seed): ?array
    {
        $site = Site::where('old_model', PPCSite::class)
            ->where('old_id', $seed->site_id)
            ->first();

        if (! empty($site)) {
            return [
                'old_model' => SeedDetail::class,
                'old_id' => $seed->id,
                'name' => data_get($seed, 'name'),
                'weight_of_sample' => data_get($seed, 'weight_of_sample'),
                'amount' => data_get($seed, 'weight_of_sample'),
                'seeds_in_sample' => data_get($seed, 'seeds_in_sample'),
                'seedable_id' => $site->id,
                'seedable_type' => Site::class,
            ];
        }

        return null;
    }

    private function mapDirectSeedingValues(DirectSeeding $seed): ?array
    {
        $report = SiteReport::where('old_model', SiteSubmission::class)
            ->where('old_id', $seed->site_submission_id)
            ->first();

        if (! empty($report)) {
            return [
                'old_model' => DirectSeeding::class,
                'old_id' => $seed->id,

                'name' => data_get($seed, 'name'),
                'weight_of_sample' => data_get($seed, 'weight'),
                'amount' => data_get($seed, 'weight'),
                'seedable_id' => $report->id,
                'seedable_type' => SiteReport::class,
            ];
        }

        return null;
    }
}
