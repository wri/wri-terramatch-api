<?php

namespace App\Exports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class MatchesExport implements FromQuery, WithHeadings
{
    use Exportable;

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'offer_id',
            'pitch_id',
            'organisation_id',
            'country',
            'state',
            'city',
            'created_at',
        ];
    }

    /**
     * @return Builder
     */
    public function query()
    {
        $query =  DB::table('matches')
            ->join('interests', 'matches.primary_interest_id', '=', 'interests.id')
            ->join('organisation_versions', 'interests.organisation_id', '=', 'organisation_versions.organisation_id')
            ->where('organisation_versions.status', '=', 'approved')
            ->select(
                'interests.offer_id',
                'interests.pitch_id',
                'interests.organisation_id',
                'organisation_versions.country',
                'organisation_versions.state',
                'organisation_versions.city',
                'matches.created_at',
            )
            ->whereDate('matches.created_at', '>', Carbon::now()->subDays(28)->toDateString())
            ->orderBy('interests.offer_id');

            return $query;
    }
}
