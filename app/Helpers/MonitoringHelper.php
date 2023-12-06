<?php

namespace App\Helpers;

use App\Models\Monitoring as MonitoringModel;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class MonitoringHelper
{
    private function __construct()
    {
    }

    public static function findMonitoringIdsByPitchId(Int $id): array
    {
        $monitorings = DB::table('monitorings')
            ->join('matches', function (JoinClause $join) {
                $join->on('monitorings.match_id', '=', 'matches.id');
            })
            ->join('interests', function (JoinClause $join) {
                $join->on('matches.primary_interest_id', '=', 'interests.id');
            })
            ->where('interests.pitch_id', '=', $id)
            ->get(['monitorings.id']);

        return $monitorings->pluck('id')->toArray();
    }

    public static function findMonitoringIdsByOfferId(Int $id): array
    {
        $monitorings = DB::table('monitorings')
            ->join('matches', function (JoinClause $join) {
                $join->on('monitorings.match_id', '=', 'matches.id');
            })
            ->join('interests', function (JoinClause $join) {
                $join->on('matches.primary_interest_id', '=', 'interests.id');
            })
            ->where('interests.offer_id', '=', $id)
            ->get(['monitorings.id']);

        return $monitorings->pluck('id')->toArray();
    }

    /**
     * This method checks whether an offer or pitch's new visibility is valid or
     * not based on its related monitorings. You cannot revert to an invalid
     * visibility if you have initiated a monitoring or if you have a monitoring
     * that has progressed past the first stage. Of course if you have no
     * monitorings then you can do what you like.
     */
    public static function isNewVisibilityValid(Model $model, String $visibility): Bool
    {
        switch ($model->getEntityName()) {
            case 'Offer':
                $type = 'offer';
                $monitoringIds = MonitoringHelper::findMonitoringIdsByOfferId($model->id);
                $monitorings = MonitoringModel::whereIn('id', $monitoringIds)->get();

                break;
            case 'Pitch':
                $type = 'pitch';
                $monitoringIds = MonitoringHelper::findMonitoringIdsByPitchId($model->id);
                $monitorings = MonitoringModel::whereIn('id', $monitoringIds)->get();

                break;
            default:
                throw new Exception();
        }
        $hasValidVisibility = in_array($visibility, ['partially_invested_funded', 'fully_invested_funded']);
        foreach ($monitorings as $monitoring) {
            $isInitiator = $monitoring->initiator == $type;
            $hasProgressedPastFirstStage = $monitoring->stage != 'awaiting_visibilities';
            if (
                ($isInitiator && ! $hasValidVisibility) ||
                (! $isInitiator && $hasProgressedPastFirstStage && ! $hasValidVisibility)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * This method will progress a monitoring's stage from awaiting_visibilities
     * to awaiting_targets if the pitch or offer is not the initiator. It is used
     * when waiting on a pitch or offer to update their visibility so that
     * monitoring can begin.
     */
    public static function progressRelatedMonitoringStages(Model $model, String $visibility): Void
    {
        switch ($model->getEntityName()) {
            case 'Offer':
                $type = 'offer';
                $monitoringIds = MonitoringHelper::findMonitoringIdsByOfferId($model->id);
                $monitorings = MonitoringModel::whereIn('id', $monitoringIds)->get();

                break;
            case 'Pitch':
                $type = 'pitch';
                $monitoringIds = MonitoringHelper::findMonitoringIdsByPitchId($model->id);
                $monitorings = MonitoringModel::whereIn('id', $monitoringIds)->get();

                break;
            default:
                throw new Exception();
        }
        $hasValidVisibility = in_array($visibility, ['partially_invested_funded', 'fully_invested_funded']);
        foreach ($monitorings as $monitoring) {
            $isInitiator = $monitoring->initiator == $type;
            $hasProgressedPastFirstStage = $monitoring->stage != 'awaiting_visibilities';
            if (! $isInitiator && ! $hasProgressedPastFirstStage && $hasValidVisibility) {
                $monitoring->stage = 'negotiating_targets';
                $monitoring->saveOrFail();
            }
        }
    }

    public static function findMonitoringsByOrganisation(Int $id): array
    {
        $monitorings = DB::select(
            '
                SELECT
                    `monitorings`.*
                FROM `monitorings`
                LEFT JOIN `matches`
                    ON `monitorings`.`match_id` = `matches`.`id`
                LEFT JOIN `interests` AS `primary_interests` 
                    ON `matches`.`primary_interest_id` = `primary_interests`.`id`
                LEFT JOIN `interests` AS `secondary_interests` 
                    ON `matches`.`secondary_interest_id` = `secondary_interests`.`id`  
                WHERE `primary_interests`.`organisation_id` = ?
                OR `secondary_interests`.`organisation_id` = ?;
            ',
            [$id, $id]
        );

        return $monitorings;
    }
}
