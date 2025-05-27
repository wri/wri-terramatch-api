<?php

namespace App\Mail;

use App\Models\V2\PolygonUpdates;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\Polyline;


class PolygonUpdateNotification extends I18nMail
{
    private $user;

    private $sitePolygon;

    private $isManager;

    public function __construct($user, SitePolygon $sitePolygon, $isManager)
    {
        $this->user = $user;
        $this->sitePolygon = $sitePolygon;
        $this->isManager = $isManager;
        parent::__construct($user);
        $params = $this->getBodyParams();

        $this->setSubjectKey('terrafund-polygon-update.subject')
            ->setBodyKey('terrafund-polygon-update.' . ($this->isManager ? 'manager' : 'pd') . '.body')
            ->setBodyParams($params);

        $this->setTitleKey('terrafund-polygon-update.title');
        $this->setTitleParams(['{date}' => now()->format('d/m/Y')]);
    }

    private function getBodyParams(): array
    {
        $project = $this->sitePolygon->project;
        $site = $this->sitePolygon->site;
        $polygonName = $this->sitePolygon->poly_name;
        $versionId = $this->sitePolygon->version_name ?? 'No id';

        $params = [
            '{userName}' => $this->user->full_name,
            '{projectName}' => $project->name,
        ];

        $statusChanges = PolygonUpdates::where('site_polygon_uuid', $this->sitePolygon->uuid)->lastWeek()->isStatus()->get();
        $updateChanges = PolygonUpdates::where('site_polygon_uuid', $this->sitePolygon->uuid)->lastWeek()->isUpdate()->get();

        $hasUpdateChange = $updateChanges->count() > 0;
        $hasStatusChange = $statusChanges->count() > 0;

        $params['{hasUpdateChange}'] = $hasUpdateChange ? 'block' : 'none';
        $params['{hasStatusChange}'] = $hasStatusChange ? 'block' : 'none';

        if ($hasUpdateChange) {

            $sitePolygonUpdate = $updateChanges->first();
            $sitePolygonBefore = SitePolygon::where('version_name', $sitePolygonUpdate->version_name)->first();
            Log::info('sitePolygonBefore', [$sitePolygonBefore->uuid]);
            $sitePolygonAfter = SitePolygon::isUuid($this->sitePolygon->uuid)->where('created_at', '<', $sitePolygonBefore->created_at)->first();
            Log::info('sitePolygonAfter', [$sitePolygonAfter->uuid]);

            if ($sitePolygonBefore && $sitePolygonAfter) {
                $beforeCoordinates = $this->getSitePolygonCoordinates($sitePolygonBefore);
                $afterCoordinates = $this->getSitePolygonCoordinates($sitePolygonAfter);

                //check if two arrays are the same
                if ($this->areArraysEqual($beforeCoordinates, $afterCoordinates)) {
                    Log::info(message: 'Before and after coordinates are the same');
                } else {
                    $this->storeMapboxImage($beforeCoordinates, 'before.png');
                    $this->storeMapboxImage($afterCoordinates, 'after.png');

                    $this->addAttachment([
                        'imagePath' => Storage::disk('public')->path('before.png'),
                        'cid' => 'before',
                        'mime' => 'image/png'
                    ]);
                    $this->addAttachment([
                        'imagePath' => Storage::disk('public')->path('after.png'),
                        'cid' => 'after',
                        'mime' => 'image/png'
                    ]);
                }
            } else {
                Log::info('No site polygon before or after found');
            }

            $params['{polygonUpdateTable}'] = $this->getTable(
                'update',
                $project->name,
                $site->name,
                $polygonName,
                $versionId,
                $updateChanges
            );
        }

        if ($hasStatusChange) {
            $params['{polygonStatusTable}'] = $this->getTable(
                'status',
                $project->name,
                $site->name,
                $polygonName,
                $versionId,
                $statusChanges
            );
        }

        return $params;
    }

    public function getTable($type, $projectName, $siteName, $polygonName, $versionId, $polygonUpdates): string
    {
        $rows = [];

        foreach ($polygonUpdates as $polygonUpdateRecord) {
            if ($type === 'status') {
                $rows[] = $this->getStatusRow(
                    $projectName,
                    $siteName,
                    $polygonName,
                    $polygonUpdateRecord
                );
            } else {
                $rows[] = $this->getUpdateRow(
                    $projectName,
                    $siteName,
                    $polygonName,
                    $versionId,
                    $polygonUpdateRecord
                );
            }
        }

        return implode('', $rows);
    }

    private function getStatusRow($projectName, $siteName, $polygonName, $polygonUpdateRecord): string
    {
        $link = $this->getLink();

        return '<tr>' .
            '<td style="border: 1px solid #ddd; padding: 8px; text-align: center; border-left:hidden; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $projectName .'</td>' .
            '<td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $siteName .'</td>' .
            '<td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;"><a href="'.$link.'">'. $polygonName .'</a></td>' .
            '<td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $this->transformSnakeCaseToTitleCase($polygonUpdateRecord->old_status) .'</td>' .
            '<td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $this->transformSnakeCaseToTitleCase($polygonUpdateRecord->new_status) .'</td>' .
            ($this->isManager ? ('<td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $polygonUpdateRecord->user->full_name .'</td>') : '').
            '<td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; border-right:hidden; word-break: break-word;">'. $polygonUpdateRecord->comment .'</td>' .
        '</tr>';
    }

    private function getUpdateRow($projectName, $siteName, $polygonName, $versionId, $polygonUpdateRecord)
    {
        $link = $this->getLink();

        return '<tr>' .
            '<td style="border: 1px solid #ddd; padding: 8px; text-align: center; border-left:hidden; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $projectName .'</td>' .
            '<td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $siteName .'</td>' .
            '<td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;"><a href="'.$link.'">'. $polygonName .'</a></td>' .
            '<td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $versionId .'</td>' .
            '<td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $polygonUpdateRecord->change .'</td>' .
            ($this->isManager ? ('<td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $polygonUpdateRecord->user->full_name .'</td>') : '').
            '<td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; border-right:hidden; word-break: break-word;">'. $polygonUpdateRecord->comment .'</td>' .
        '</tr>';
    }

    private function transformSnakeCaseToTitleCase(string $string): string
    {
        return ucwords(str_replace('_', ' ', $string));
    }

    private function getLink(): string
    {
        $link = config('app.front_end');
        if ($this->isManager) {
            $link .= '/admin#/' . 'site' . '/' . $this->sitePolygon->site_id . '/show/1';
        } else {
            $link .= '/site/' . $this->sitePolygon->site_id;
        }

        return $link;
    }

    private function getSitePolygonCoordinates(SitePolygon $sitePolygon)
    {
        $sitePolygon = SitePolygon::isUuid($sitePolygon->uuid)->first();
        $polygonGeometry = PolygonGeometry::where('uuid', operator: $sitePolygon->poly_id)
                    ->select('uuid', DB::raw('ST_AsGeoJSON(geom) AS geojsonGeometry'))
                    ->first();
        $geometry = json_decode($polygonGeometry->geojsonGeometry, true);
        return array_map(function ($item) {
            return [$item[1], $item[0]];
        }, $geometry['coordinates'][0]);
    }

    private function storeMapboxImage(array $coordinates, string $imageName)
    {
        $encoded = Polyline::encode($coordinates);
        $url = "https://api.mapbox.com/styles/v1/mapbox/streets-v12/static/path-2+f44-0.5+fff-0.5($encoded)/auto/500x300";
        $token = "pk.eyJ1IjoidGVycmFtYXRjaCIsImEiOiJjbHN4b2drNnAwNHc0MnBtYzlycmQ1dmxlIn0.ImQurHBtutLZU5KAI5rgng";

        Log::info('url', [$url]);

        $response = Http::withOptions(['stream' => true])->get($url, [
            'access_token' => $token,
        ]);

        $imagePath = $imageName;
        Log::info('imagePath', [$imagePath]);
        Storage::disk('public')->put($imagePath, $response->body());
    }

    private function areArraysEqual(array $array1, array $array2): bool
    {
        Log::info('array1', [$array1]);
        return json_encode($array1) === json_encode($array2);
    }

}
