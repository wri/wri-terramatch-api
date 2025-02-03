<?php

namespace App\Http\Controllers\V2\Entities;

use App\Http\Controllers\Controller;
use App\Models\V2\EntityModel;
use App\Models\V2\Projects\Project;
use App\Models\V2\Seeding;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetAggregateReportsController extends Controller
{
    private const SUPPORTED_ENTITIES = [
        'project' => Project::class,
        'site' => Site::class,
    ];

    private const FRAMEWORK_COLLECTIONS = [
        'terrafund' => ['tree-planted','tree-regenerating'],
        'ppc' => ['tree-planted', 'seeding-records', 'tree-regenerating'],
        'hbf' => ['tree-planted', 'seeding-records', 'tree-regenerating'],
    ];

    public function __invoke(Request $request, string $entityType, string $uuid): JsonResponse
    {
        if (! array_key_exists($entityType, self::SUPPORTED_ENTITIES)) {
            return response()->json(['error' => 'Unsupported entity type'], 400);
        }

        $entityClass = self::SUPPORTED_ENTITIES[$entityType];
        $entity = $entityClass::where('uuid', $uuid)->firstOrFail();

        $this->authorize('read', $entity);

        $frameworkKey = $entity->framework_key;
        if (! array_key_exists($frameworkKey, self::FRAMEWORK_COLLECTIONS)) {
            return response()->json(['error' => 'Unsupported framework'], 400);
        }

        $reportIds = $this->getApprovedReportIds($entity);

        $response = $this->initializeResponse($frameworkKey);

        if (in_array('tree-planted', self::FRAMEWORK_COLLECTIONS[$frameworkKey])) {
            $this->processTreeSpecies($reportIds, $response);
        }

        if (in_array('tree-regenerating', self::FRAMEWORK_COLLECTIONS[$frameworkKey])) {
            $this->processTreesRegenerating($reportIds, $response);
        }

        if (in_array('seeding-records', self::FRAMEWORK_COLLECTIONS[$frameworkKey])) {
            $this->processSeedings($reportIds, $response);
        }

        return response()->json($response);
    }

    private function getApprovedReportIds(EntityModel $entity): array
    {
        if ($entity instanceof Project) {
            return $entity->approvedSiteReportIds()->pluck('id')->toArray();
        } elseif ($entity instanceof Site) {
            return $entity->approvedReportIds()->pluck('id')->toArray();
        }
    }

    private function initializeResponse(string $frameworkKey): array
    {
        $response = [];
        foreach (self::FRAMEWORK_COLLECTIONS[$frameworkKey] as $collection) {
            $responseKey = $collection === 'tree-planted' ? 'tree-planted' : 'seeding-records';
            $response[$responseKey] = [];
        }

        return $response;
    }

    private function processTreesRegenerating(array $reportIds, array &$response): void
    {
        $siteReports = SiteReport::query()
            ->whereIn('id', $reportIds)
            ->get();
        $grouped = $siteReports
            ->groupBy('due_at')
            ->map(function ($group) {
                return [
                    'dueDate' => $group->first()->due_at,
                    'aggregateAmount' => $group->sum('num_trees_regenerating'),
                ];
            })
            ->values()
            ->sortBy('dueDate')
            ->toArray();
        $response['trees-regenerating'] = array_values($grouped);

    }

    private function processTreeSpecies(array $reportIds, array &$response): void
    {
        $treeSpecies = TreeSpecies::query()
            ->where('speciesable_type', SiteReport::class)
            ->whereIn('speciesable_id', $reportIds)
            ->where('collection', 'tree-planted')
            ->where('hidden', false)
            ->get();

        $grouped = $treeSpecies
            ->groupBy(function ($species) {
                return $species->speciesable->due_at;
            })
            ->map(function ($group) {
                return [
                    'dueDate' => $group->first()->speciesable->due_at,
                    'aggregateAmount' => $group->sum('amount'),
                ];
            })
            ->values()
            ->sortBy('dueDate')
            ->toArray();

        $response['tree-planted'] = array_values($grouped);
    }

    private function processSeedings(array $reportIds, array &$response): void
    {
        $seedings = Seeding::query()
            ->where('seedable_type', SiteReport::class)
            ->whereIn('seedable_id', $reportIds)
            ->where('hidden', false)
            ->get();

        $grouped = $seedings
            ->groupBy(function ($seeding) {
                return $seeding->seedable->due_at;
            })
            ->map(function ($group) {
                return [
                    'dueDate' => $group->first()->seedable->due_at,
                    'aggregateAmount' => $group->sum('amount'),
                ];
            })
            ->values()
            ->sortBy('dueDate')
            ->toArray();

        $response['seeding-records'] = array_values($grouped);
    }
}
