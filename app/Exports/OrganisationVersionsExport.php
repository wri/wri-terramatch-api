<?php

namespace App\Exports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class OrganisationVersionsExport implements FromQuery, WithHeadings
{
    use Exportable;

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'id', 'name', 'category', 'country', 'state', 'city', 'created_at', 'status', 'rejected_reason',
            'approved_rejected_by', 'approved_rejected_at',
        ];
    }

    /**
     * @return Builder
     */
    public function query()
    {
        $query = DB::table('organisation_versions')
            ->select([
                'organisations.id',
                'organisation_versions.name',
                'organisation_versions.category',
                'organisation_versions.country',
                'organisation_versions.state',
                'organisation_versions.city',
                'organisations.created_at',
                'organisation_versions.status',
                'organisation_versions.rejected_reason',
                'organisation_versions.approved_rejected_by',
                'organisation_versions.approved_rejected_at',
            ])
            ->join('organisations', 'organisation_versions.organisation_id', '=', 'organisations.id')
            ->orderBy('organisations.id');

        return $query;
    }
}
