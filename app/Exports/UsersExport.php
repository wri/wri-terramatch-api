<?php

namespace App\Exports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class UsersExport implements FromQuery, WithHeadings
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
        $lastOrganisationVersion = DB::table('organisation_versions')
            ->select(DB::raw('MAX(id) as id, organisation_id as oid'))
            ->whereIn('status', ['approved', 'pending'])
            ->groupBy('oid');

        $query = DB::table('users')
            ->select([
                'users.id',
                'organisation_versions.category',
                'organisation_versions.country',
                'organisation_versions.state',
                'organisation_versions.city',
                'users.created_at',
                'organisation_versions.status'
            ])
            ->join('organisations', 'users.organisation_id', '=', 'organisations.id')
            ->join('organisation_versions', 'organisation_versions.organisation_id', '=', 'organisations.id')
            ->joinSub($lastOrganisationVersion, 'latest_versions', function ($join) {
                $join->on('organisation_versions.id', '=', 'latest_versions.id');
            })
            ->whereDate('users.created_at', '>', Carbon::now()->subDays(28)->toDateString())
            ->orderBy('users.id');

        return $query;
    }
}
