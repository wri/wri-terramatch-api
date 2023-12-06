<?php

namespace App\Console\Commands\Migration;

use App\Models\Invasive;
use App\Models\Site as PPCSite;
use App\Models\V2\Invasive as V2Invasive;
use App\Models\V2\Sites\Site;
use Illuminate\Console\Command;

class InvasiveMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:invasive {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Invasive Data only to  V2 tables';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            V2Invasive::truncate();
        }

        $collection = Invasive::all();
        foreach ($collection as $invasive) {
            $map = $this->mapInvasiveValues($invasive);
            if (! empty($map)) {
                $new = V2Invasive::create($map);

                if ($this->option('timestamps')) {
                    $new->created_at = $invasive->created_at;
                    $new->updated_at = $invasive->updated_at;
                    $new->save();
                }
            }
        }

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }

    private function mapInvasiveValues(Invasive $invasive): ?array
    {
        $site = Site::where('old_model', PPCSite::class)
            ->where('old_id', $invasive->site_id)
            ->first();

        if (! empty($site)) {
            return [
                'old_model' => Invasive::class,
                'old_id' => $invasive->id,

                'type' => data_get($invasive, 'type'),
                'name' => data_get($invasive, 'name'),
                'invasiveable_id' => $site->id,
                'invasiveable_type' => Site::class,
            ];
        }

        return null;
    }
}
