<?php

namespace App\Services;

use App\Http\Resources\V2\User\ActionResource;
use App\Models\V2\Action;
use App\Models\V2\FinancialReport;
use App\Models\V2\User;

class MyActionsService
{
    public function getPendingActionsPayloadForUser(User $user): array
    {
        $projectIds = $user->projects()->pluck('v2_projects.id')->toArray();

        $qry = Action::query()
            ->with('targetable')
            ->whereHas('targetable')
            ->pending()
            ->projectIds($projectIds);

        $projectActions = $qry->get();

        $organisationId = optional($user->organisation)->id;
        if ($organisationId) {
            $financialReportIds = FinancialReport::where('organisation_id', $organisationId)->pluck('id')->toArray();
            $financialReportActions = Action::query()
                ->with('targetable')
                ->whereHas('targetable')
                ->pending()
                ->where('targetable_type', FinancialReport::class)
                ->whereIn('targetable_id', $financialReportIds)
                ->get();
        } else {
            $financialReportActions = collect();
        }

        $actions = $projectActions->concat($financialReportActions)
            ->sortByDesc('updated_at')
            ->take(1000)
            ->values();

        return ActionResource::collection($actions)->resolve();
    }
}
