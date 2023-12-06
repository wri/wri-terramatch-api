<?php

namespace App\Console\Commands;

use App\Models\Organisation;
use App\Models\V2\Organisation as V2Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class V2MigratePendingOrganisationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-migrate-pending-organisations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update legacy pending organisations to V2';

    public function handle()
    {
        $collection = Organisation::all();
        foreach ($collection as $organisation) {
            $version = $organisation->versions->where('status', 'pending')->sortByDesc('created_at')->first();
            if (empty($organisation->approved_version) && ! empty($version)) {
                $organisation->uuid = $organisation->uuid ?? Str::uuid()->toString();
                $organisation->name = $organisation->name ?? $version->name;
                $organisation->type = $organisation->type ?? $version->type;
                $organisation->status = V2Organisation::STATUS_PENDING;
                $organisation->phone = $organisation->phone ?? $version->phone_number;

                $organisation->hq_street_1 = $organisation->hq_street_1 ?? $version->address_1;
                $organisation->hq_street_2 = $organisation->hq_street_2 ?? $version->address_2;
                $organisation->hq_city = $organisation->hq_city ?? $version->city;
                $organisation->hq_state = $organisation->hq_state ?? $version->state;
                $organisation->hq_zipcode = $organisation->hq_zipcode ?? $version->zip_code;
                $organisation->hq_country = $organisation->hq_country ?? $version->country;

                $organisation->founding_date = $organisation->founding_date ?? $version->founded_at;
                $organisation->description = $organisation->description ?? $version->description;

                $organisation->web_url = $organisation->web_url ?? $version->website;
                $organisation->facebook_url = $organisation->facebook_url ?? $version->facebook;
                $organisation->twitter_url = $organisation->twitter_url ?? $version->twitter;
                $organisation->linkedin_url = $organisation->linkedin_url ?? $version->linkedin;
                $organisation->instagram_url = $organisation->instagram_url ?? $version->instagram;

                $organisation->relevant_experience_years = $organisation->relevant_experience_years ?? $version->monitoring_and_evaluation_experience;
                $organisation->ha_restored_total = $organisation->ha_restored_total ?? $version->total_hectares_restored;
                $organisation->ha_restored_3year = $organisation->ha_restored_3year ?? $version->hectares_restored_three_years;
                $organisation->trees_grown_total = $organisation->trees_grown_total ?? $version->total_trees_grown;
                $organisation->tree_care_approach = $organisation->tree_care_approach ?? $version->tree_maintenance_and_aftercare;

                $organisation->save();
            }
        }
    }
}
