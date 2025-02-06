<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeWorkdayRestorationPartner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:merge-workday-restoration-partner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected const RESTORATION_PARTNER_CLASS = 'App\\Models\\V2\\RestorationPartners\\RestorationPartner';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // There are fewer than 150 non-deleted records at the time of this writing, so no need for batches
        $partners = DB::table('restoration_partners')->where('deleted_at', null)->get();

        $this->info('Moving Restoration Partners to Demographics...');
        $this->withProgressBar($partners->count(), function ($progressBar) use ($partners) {
            foreach ($partners as $partner) {
                $demographic = Demographic::create([
                    // Need to preserve the UUID
                    'uuid' => $partner->uuid,
                    'demographical_type' => $partner->partnerable_type,
                    'demographical_id' => $partner->partnerable_id,
                    'type' => Demographic::RESTORATION_PARTNER_TYPE,
                    'collection' => $partner->collection,
                    'description' => $partner->description,
                    'hidden' => $partner->hidden,
                ]);

                DemographicEntry::where([
                    'demographical_type' => self::RESTORATION_PARTNER_CLASS,
                    'demographic_id' => $partner->id,
                ])->update([
                    'demographical_type' => null,
                    'demographic_id' => $demographic->id,
                ]);

                $progressBar->advance();
            }
        });

        $this->info("\n\nPermanently removing demographic entries with RestorationPartner type...");
        DemographicEntry::withTrashed()->where('demographical_type', self::RESTORATION_PARTNER_CLASS)->forceDelete();

        $this->info("\nDone");
    }
}
