<?php

namespace App\Helpers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class InterestHelper
{
    private function __construct()
    {
    }

    public static function findInitiated(Int $organisationId): array
    {
        $visibilities = ['archived', 'finished'];
        $ids = DB::table('interests')
            ->join('offers', function (JoinClause $join) {
                $join->on('interests.offer_id', '=', 'offers.id');
            })
            ->join('pitches', function (JoinClause $join) {
                $join->on('interests.pitch_id', '=', 'pitches.id');
            })
            ->where('interests.organisation_id', '=', $organisationId)
            ->where('interests.has_matched', '=', false)
            ->whereNotIn('offers.visibility', $visibilities)
            ->whereNotIn('pitches.visibility', $visibilities)
            ->get(['interests.id']);

        return $ids->pluck('id')->toArray();
    }

    public static function findReceived(Int $organisationId): array
    {
        $visibilities = ['archived', 'finished'];
        $ids = DB::table('interests')
            ->join('offers', function (JoinClause $join) {
                $join->on('interests.offer_id', '=', 'offers.id');
            })
            ->join('pitches', function (JoinClause $join) {
                $join->on('interests.pitch_id', '=', 'pitches.id');
            })
            ->where('interests.organisation_id', '!=', $organisationId)
            ->where(function ($query) use ($organisationId) {
                $query
                    ->where('offers.organisation_id', '=', $organisationId)
                    ->orWhere('pitches.organisation_id', '=', $organisationId);
            })
            ->where('interests.has_matched', '=', false)
            ->whereNotIn('offers.visibility', $visibilities)
            ->whereNotIn('pitches.visibility', $visibilities)
            ->get(['interests.id']);

        return $ids->pluck('id')->toArray();
    }
}
