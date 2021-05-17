<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Match as MatchModel;
use App\Models\Interest as InterestModel;
use App\Helpers\MatchHelper;
use App\Jobs\NotifyMatchJob;
use Illuminate\Support\Facades\Config;

class FindMatchesCommand extends Command
{
    protected $signature = "find-matches";
    protected $description = "Finds matches";

    public function handle(): int
    {
        $interests = MatchHelper::findMatchingInterests();
        foreach ($interests as $interest) {
            $ids = explode(",", $interest->ids);
            $primaryInterest = InterestModel::findOrFail($ids[0]);
            $secondaryInterest = InterestModel::findOrFail($ids[1]);
            MatchHelper::assertInterestsMatch($primaryInterest, $secondaryInterest);
            $match = new MatchModel();
            $match->primary_interest_id = $primaryInterest->id;
            $match->secondary_interest_id = $secondaryInterest->id;
            $match->saveOrFail();
            $primaryInterest->matched = true;
            $primaryInterest->saveOrFail();
            $secondaryInterest->matched = true;
            $secondaryInterest->saveOrFail();
            NotifyMatchJob::dispatch($match);
        }
        return 0;
    }
}
