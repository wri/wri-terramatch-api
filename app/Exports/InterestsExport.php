<?php

namespace App\Exports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class InterestsExport implements FromQuery, WithHeadings
{
    use Exportable;

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'id', 'organisation_id','category','country','state','city','initiator','offer_id','pitch_id','created_at'
        ];
    }

    /**
     * @return Builder
     */
    public function query()
    {
        $query =  DB::table('interests')
            ->join('organisation_versions', 'organisation_versions.organisation_id', '=', 'interests.organisation_id')
            ->where('organisation_versions.status', '=', 'approved')
            ->select(['interests.id', 'interests.organisation_id','organisation_versions.category','organisation_versions.country','organisation_versions.state','organisation_versions.city','interests.initiator','interests.offer_id','interests.pitch_id','interests.created_at'])
            ->whereDate('created_at', '>', Carbon::now()->subDays(28)->toDateString())
            ->orderBy('interests.id');

        return $query;
    }
}
