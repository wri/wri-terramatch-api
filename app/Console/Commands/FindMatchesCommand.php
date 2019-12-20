<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;
use App\Models\Match as MatchModel;
use App\Models\Interest as InterestModel;
use App\Services\MatchService;

class FindMatchesCommand extends Command
{
    protected $signature = "find-matches";
    protected $description = "Finds matches";

    private $matchModel = null;
    private $interestModel = null;
    private $matchService = null;
    private $notificationService = null;

    public function __construct(
        MatchModel $matchModel,
        InterestModel $interestModel,
        MatchService $matchService,
        NotificationService $notificationService
    ) {
        parent::__construct();
        $this->matchModel = $matchModel;
        $this->interestModel = $interestModel;
        $this->matchService = $matchService;
        $this->notificationService = $notificationService;
    }

    public function handle(): int
    {
        $interests = $this->matchService->findMatchingInterests();
        foreach ($interests as $interest) {
            $ids = explode(",", $interest->ids);
            $primaryInterest = $this->interestModel->findOrFail($ids[0]);
            $secondaryInterest = $this->interestModel->findOrFail($ids[1]);
            $this->matchService->assertInterestsMatch($primaryInterest, $secondaryInterest);
            $match = $this->matchModel->newInstance();
            $match->primary_interest_id = $primaryInterest->id;
            $match->secondary_interest_id = $secondaryInterest->id;
            $match->saveOrFail();
            $primaryInterest->matched = true;
            $primaryInterest->saveOrFail();
            $secondaryInterest->matched = true;
            $secondaryInterest->saveOrFail();
            $this->notificationService->notifyMatch($match);
        }
        return 0;
    }
}
