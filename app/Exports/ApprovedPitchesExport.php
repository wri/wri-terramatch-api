<?php

namespace App\Exports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class ApprovedPitchesExport implements FromQuery, WithHeadings
{
    use Exportable;

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'id', 'category','country','state','city','created_at', 'status'
        ];
    }

    /**
     * @return Builder
     */
    public function query()
    {
        $query =  DB::table('pitches')
            ->join('pitch_versions', 'pitch_versions.pitch_id', '=', 'pitches.id')
            ->join('organisation_versions', 'organisation_versions.organisation_id', '=', 'pitches.organisation_id')
            ->where('pitch_versions.status', '=', 'approved')
            ->where('organisation_versions.status', '=', 'approved')
            ->select(['pitches.id','organisation_versions.category','organisation_versions.country','organisation_versions.state','organisation_versions.city','pitches.created_at', 'pitch_versions.status'])
            ->whereDate('created_at', '>', Carbon::now()->subDays(28)->toDateString())
            ->orderBy('pitches.id');

        return $query;
    }
}
