<?php

namespace App\Console\Commands;

use App\Helpers\MatchHelper;
use App\Jobs\NotifyMatchJob;
use App\Models\Interest as InterestModel;
use App\Models\Matched as MatchModel;
use Illuminate\Console\Command;

class FindMatchesCommand extends Command
{
    protected $signature = 'find-matches';

    protected $description = 'Finds matches';

    public function handle(): int
    {
        $interests = MatchHelper::findMatchingInterests();
        foreach ($interests as $interest) {
            $ids = explode(',', $interest->ids);
            $primaryInterest = InterestModel::findOrFail($ids[0]);
            $secondaryInterest = InterestModel::findOrFail($ids[1]);
            MatchHelper::assertInterestsMatch($primaryInterest, $secondaryInterest);
            $match = new MatchModel();
            $match->primary_interest_id = $primaryInterest->id;
            $match->secondary_interest_id = $secondaryInterest->id;
            $match->saveOrFail();
            $primaryInterest->has_matched = true;
            $primaryInterest->saveOrFail();
            $secondaryInterest->has_matched = true;
            $secondaryInterest->saveOrFail();
            NotifyMatchJob::dispatch($match);
        }

        return 0;
    }
}
