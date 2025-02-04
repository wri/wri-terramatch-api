<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Demographics\DemographicEntry;
use Illuminate\Console\Command;

class FixDemographicNameType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:fix-demographic-name-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Moves several system-defined names from the name column to type in demographic_entities';

    protected const UPDATED_MAPPINGS = [
        DemographicEntry::GENDER => DemographicEntry::GENDERS,
        DemographicEntry::AGE => DemographicEntry::AGES,
        DemographicEntry::CASTE => DemographicEntry::CASTES,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (self::UPDATED_MAPPINGS as $type => $subtypes) {
            foreach ($subtypes as $subtype) {
                $this->info("Updating subtype mapping [type=$type, subtype=$subtype]");
                DemographicEntry::withTrashed()
                    ->where(['type' => $type, 'name' => $subtype])
                    ->update(['subtype' => $subtype, 'name' => null, 'updated_at' => 'now()']);
            }
        }
    }
}
