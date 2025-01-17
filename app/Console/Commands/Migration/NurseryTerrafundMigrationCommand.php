<?php

namespace App\Console\Commands\Migration;

use App\Models\Terrafund\TerrafundNursery;
use App\Models\V2\Nurseries\Nursery;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Console\Command;

class NurseryTerrafundMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:nursery-terrafund {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Terrafund Nursery Data only to  V2 Nursery';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            Nursery::truncate();
        }

        $collection = TerrafundNursery::all();

        foreach ($collection as $nursery) {
            $count++;
            $map = $this->mapValues($nursery);

            $newNursery = Nursery::create($map);
            $created++;

            if ($this->option('timestamps')) {
                $newNursery->created_at = $nursery->created_at;
                $newNursery->updated_at = $nursery->updated_at;
                $newNursery->save();
            }
        }

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }

    private function mapValues(TerrafundNursery $nursery): array
    {
        $data = [
            'old_model' => TerrafundNursery::class,
            'old_id' => $nursery->id,
            'framework_key' => 'terrafund',

            'name' => data_get($nursery, 'name'),
            'start_date' => data_get($nursery, 'start_date'),
            'end_date' => data_get($nursery, 'end_date'),
            'status' => EntityStatusStateMachine::APPROVED,
            'seedling_grown' => data_get($nursery, 'seedling_grown'),
            'planting_contribution' => data_get($nursery, 'planting_contribution'),
            'type' => data_get($nursery, 'nursery_type'),
        ];

        return $data;
    }
}
