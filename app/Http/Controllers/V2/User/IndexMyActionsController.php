<?php

namespace App\Http\Controllers\V2\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\User\ActionResource;
use App\Models\V2\Action;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class IndexMyActionsController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $user = Auth::user();

        $projectIds = $user->projects()->pluck('v2_projects.id')->toArray();

        $statuses = ['needs-more-information', 'due'];

        $reportActions = Action::query()
            ->with('targetable')
            ->pending()
            ->whereHasMorph('targetable', [
                ProjectReport::class,
                SiteReport::class,
                NurseryReport::class,
            ], function ($query) use ($statuses) {
                $query->whereIn('status', $statuses);
            })
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->projectIds($projectIds)
            ->get();

        $entityActions = Action::query()
            ->with('targetable')
            ->pending()
            ->whereHasMorph('targetable', [
                Project::class,
                Site::class,
                Nursery::class,
            ], function ($query) use ($statuses) {
                $query->whereIn('status', $statuses);
            })
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->projectIds($projectIds)
            ->get();

        $actions = $reportActions->merge($entityActions);

        return ActionResource::collection($actions);
    }
}
