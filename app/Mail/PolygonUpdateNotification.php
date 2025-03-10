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
            ->setBodyKey('terrafund-polygon-update.body')
            ->setBodyParams($params);

        if ($isManager) {
            $this->setTitleKey('terrafund-polygon-update.dqatopd.title');
        } else {
            $this->setTitleKey('terrafund-polygon-update.pdtodqa.title');
        }
    }

    private function getBodyParams(): array
    {
        $params = [
            '{userName}' => $this->user->full_name,
        ];

        $project = $this->sitePolygon->project;
        $site = $this->sitePolygon->site;
        $polygonName = $this->sitePolygon->poly_name;
        $versionId = $this->sitePolygon->version_name;

        $statusChanges = PolygonUpdates::where('site_polygon_uuid', $this->sitePolygon->uuid)->lastWeek()->isStatus()->get();
        $updateChanges = PolygonUpdates::where('site_polygon_uuid', $this->sitePolygon->uuid)->lastWeek()->isUpdate()->get();

        $hasUpdateChange = $updateChanges->count() > 0;
        $hasStatusChange = $statusChanges->count() > 0;

        $params['{hasUpdateChange}'] = $hasUpdateChange ? 'block' : 'none';
        $params['{hasStatusChange}'] = $hasStatusChange ? 'block' : 'none';

        if ($hasUpdateChange) {
            $params['{polygonUpdateTable}'] = $this->getTable(
                $project->name,
                $site->name,
                $polygonName,
                $versionId,
                $updateChanges
            );
        }

        if ($hasStatusChange) {
            $params['{polygonStatusTable}'] = $this->getTable(
                $project->name,
                $site->name,
                $polygonName,
                $versionId,
                $statusChanges
            );
        }

        return $params;
    }

    public function getTable($projectName, $siteName, $polygonName, $versionId, $list): string
    {
        $rows = [];

        foreach ($list as $item) {

            $rows[] = $this->getRow(
                $projectName,
                $siteName,
                $polygonName,
                $versionId,
                $item->change,
                $item->user->full_name,
                $item->comment
            );
        }

        return implode('', $rows);
    }

    private function getRow($projectName, $siteName, $polygonName, $versionId, $change, $updatedBy, $comment): string
    {
        return '<tr>' .
        '   <td style="border: 1px solid #ddd; padding: 8px; text-align: center; border-left:hidden; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $projectName .'</td>' .
        '   <td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $siteName .'</td>' .
        '   <td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $polygonName .'</td>' .
        '   <td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $versionId .'</td>' .
        '   <td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $change .'</td>' .
        '   <td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; word-break: break-word;">'. $updatedBy .'</td>' .
        '   <td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 12px; color: #002633; font-family: \'Inter\', sans-serif; border-right:hidden; word-break: break-word;">'. $comment .'</td>' .
        '</tr>';
    }
}
