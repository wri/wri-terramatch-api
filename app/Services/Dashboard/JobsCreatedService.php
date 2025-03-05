<?php

namespace App\Services\Dashboard;

use App\Helpers\TerrafundDashboardQueryHelper;
use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Projects\ProjectReport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class JobsCreatedService
{
    public function calculateJobsCreated(Request $request)
    {
        $query = TerrafundDashboardQueryHelper::buildQueryFromRequest($request);

        $projectIds = $query->select('v2_projects.id', 'organisations.type');
        /** @var Collection $demographics */
        $demographics = Demographic::where([
            'demographical_type' => ProjectReport::class,
            'demographical_id' => ProjectReport::whereIn('project_id', $projectIds)
                ->where('status', 'approved')->select('id'),
            'visible' => true,
            'type' => 'jobs',
        ])->with('entries')->get();

        $all = $this->entries($demographics);
        $ft = $this->entries($this->forCollection($demographics, 'full-time'));
        $pt = $this->entries($this->forCollection($demographics, 'part-time'));

        return (object) [
            'totalJobsCreated' => $this->sum($this->forType($all, 'gender')),
            'total_ft' => $this->sum($this->forType($ft, 'gender')),
            'total_pt' => $this->sum($this->forType($pt, 'gender')),
            'total_men' => $this->sum($this->forType($all, 'gender', 'male')),
            'total_pt_men' => $this->sum($this->forType($pt, 'gender', 'male')),
            'total_ft_men' => $this->sum($this->forType($ft, 'gender', 'male')),
            'total_women' => $this->sum($this->forType($all, 'gender', 'female')),
            'total_pt_women' => $this->sum($this->forType($pt, 'gender', 'female')),
            'total_ft_women' => $this->sum($this->forType($ft, 'gender', 'female')),
            'total_youth' => $this->sum($this->forType($all, 'age', 'youth')),
            'total_pt_youth' => $this->sum($this->forType($pt, 'age', 'youth')),
            'total_ft_youth' => $this->sum($this->forType($ft, 'age', 'youth')),
            'total_non_youth' => $this->sum($this->forType($all, 'age', 'non-youth')),
            'total_pt_non_youth' => $this->sum($this->forType($pt, 'age', 'non-youth')),
            'total_ft_non_youth' => $this->sum($this->forType($ft, 'age', 'non-youth')),
        ];
    }

    private function sum(Collection $entries): int
    {
        return (int) $entries->pluck('amount')->sum() ?? 0;
    }

    private function entries(Collection $demographics): Collection
    {
        return $demographics->map(fn ($d) => $d->entries)->flatten();
    }

    private function forCollection(Collection $demographics, string $collection): Collection
    {
        return $demographics->filter(fn ($d) => $d->collection == $collection);
    }

    private function forType(Collection $entries, string $type, string $subtype = null): Collection
    {
        return $entries->filter(fn ($e) => $e->type == $type && ($subtype == null || $e->subtype == $subtype));
    }
}
