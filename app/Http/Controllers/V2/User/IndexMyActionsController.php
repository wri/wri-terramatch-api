<?php

namespace App\Http\Controllers\V2\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\User\ActionResource;
use App\Models\V2\Action;
use App\Models\V2\FinancialReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class IndexMyActionsController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $user = Auth::user();

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
                ->pending()
                ->whereHas('targetable')
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

        return ActionResource::collection($actions);
    }
}
