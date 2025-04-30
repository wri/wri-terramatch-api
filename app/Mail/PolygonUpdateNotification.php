<?php

namespace App\Mail;

use App\Models\V2\PolygonUpdates;
use App\Models\V2\Sites\SitePolygon;

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

    private function getStatusRow(string $projectName, string $siteName, string $polygonName, PolygonUpdates $polygonUpdateRecord): string
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
}
