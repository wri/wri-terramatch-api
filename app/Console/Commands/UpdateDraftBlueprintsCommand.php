<?php

namespace App\Console\Commands;

use App\Models\Draft;
use App\Models\Drafting\DraftOffer;
use App\Models\Drafting\DraftOrganisation;
use App\Models\Drafting\DraftPitch;
use App\Models\Drafting\DraftProgramme;
use App\Models\Drafting\DraftProgrammeSubmission;
use App\Models\Drafting\DraftSite;
use App\Models\Drafting\DraftSiteSubmission;
use App\Models\Drafting\DraftTerrafundNursery;
use App\Models\Drafting\DraftTerrafundNurserySubmission;
use App\Models\Drafting\DraftTerrafundProgramme;
use App\Models\Drafting\DraftTerrafundProgrammeSubmission;
use App\Models\Drafting\DraftTerrafundSite;
use App\Models\Drafting\DraftTerrafundSiteSubmission;
use Illuminate\Console\Command;

class UpdateDraftBlueprintsCommand extends Command
{
    protected $signature = 'update-draft-blueprints {--summary=n} {--checkonly=n}';

    protected $description = 'Updates draft blueprint to the latest. optional Parameters --summary=[y/n/v] --checkonly[y/n]';

    public function handle()
    {
        $collection = Draft::all();
        $checked = 0;
        $updated = 0;
        $summary = $this->option('summary');
        $checkonly = $this->option('checkonly');

        foreach ($collection as $draft) {
            $checked++;
            $data = json_decode($draft->data);
            $blueprint = $this->convert($this->getLatestBlueprint($draft));

            $newItem = $this->compareAndUpdate($data, $blueprint);
            if (json_encode($newItem) != $draft->data) {
                if ($summary == 'v') {
                    echo 'draft id ' .  $draft->id . ' updated.' . chr(10);
                    echo '-- ORIGINAL : ' .  $draft->data . chr(10);
                    echo '-- UPDATED : ' .  json_encode($newItem) . chr(10);
                }

                if (strtolower($checkonly) == 'n') {
                    $draft->data = json_encode($newItem);
                    $draft->save();
                }
                $updated++;
            }
        }

        if ($summary == 'y') {
            echo $checked . ' Draft files checked.' . chr(10);
            $message = $checkonly == 'y' ? ' Draft files could be updated.' : ' Draft files have been updated.';
            echo $updated . $message . chr(10);
        }

        return $updated;
    }

    private function compareAndUpdate(object $data, object $blueprint): object
    {
        $newItem = clone $blueprint;
        $this->compareAndUpdateObject($data, $blueprint, $newItem);

        return $newItem;
    }

    private function compareAndUpdateObject(object $data, object $blueprint, object $item): void
    {
        foreach ($data as $prop => $value) {
            if (property_exists($blueprint, $prop)) {
                $value = data_get($data, $prop);

                if (in_array(gettype($value), ['object'])) {
                    if (! is_null(data_get($blueprint, $prop))) {
                        $this->compareAndUpdateObject($value, data_get($blueprint, $prop), $item->$prop);
                    }
                } else {
                    $item->$prop = $value;
                }
            }
        }
    }

    private function convert(array $item): object
    {
        return json_decode(json_encode($item));
    }

    private function getLatestBlueprint(Draft $draft): array
    {
        switch ($draft->type) {
            case 'organisation':
                return DraftOrganisation::BLUEPRINT;
            case 'pitch':
                return DraftPitch::BLUEPRINT;
            case 'offer':
                return DraftOffer::BLUEPRINT;
            case 'programme':
                return DraftProgramme::BLUEPRINT;
            case 'site':
                return DraftSite::BLUEPRINT;
            case 'programme_submission':
                return DraftProgrammeSubmission::BLUEPRINT;
            case 'site_submission':
                return DraftSiteSubmission::BLUEPRINT;
            case 'terrafund_programme':
                return DraftTerrafundProgramme::BLUEPRINT;
            case 'terrafund_site':
                return DraftTerrafundSite::BLUEPRINT;
            case 'terrafund_nursery':
                return DraftTerrafundNursery::BLUEPRINT;
            case 'terrafund_programme_submission':
                return DraftTerrafundProgrammeSubmission::BLUEPRINT;
            case 'terrafund_site_submission':
                return DraftTerrafundSiteSubmission::BLUEPRINT;
            case 'terrafund_nursery_submission':
                return DraftTerrafundNurserySubmission::BLUEPRINT;
            default:
                return [];
        }
    }
}
