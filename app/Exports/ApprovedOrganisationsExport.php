<?php

namespace App\Exports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class ApprovedOrganisationsExport implements FromQuery, WithHeadings
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
        $query =  DB::table('organisations')
            ->join('organisation_versions', 'organisation_versions.organisation_id', '=', 'organisations.id')
            ->where('organisation_versions.status', '=', 'approved')
            ->select(['organisations.id','organisation_versions.category','organisation_versions.country','organisation_versions.state','organisation_versions.city','organisations.created_at', 'organisation_versions.status'])
            ->whereDate('created_at', '>', Carbon::now()->subDays(28)->toDateString())
            ->orderBy('organisations.id');

        return $query;
    }
}
