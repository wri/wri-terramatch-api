<?php

namespace App\Exports;

use App\Models\FilterRecord;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class FiltersExport implements FromQuery, WithHeadings
{
    use Exportable;

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'id',
            'user_id',
            'organisation_id',
            'search_type',
            'category',
            'country',
            'state',
            'land_types',
            'land_ownerships',
            'land_size',
            'land_continent',
            'land_country',
            'restoration_methods',
            'restoration_goals',
            'funding_sources',
            'funding_amount',
            'long_term_engagement',
            'reporting_frequency',
            'reporting_level',
            'sustainable_development_goals',
            'price_per_tree',
            'created_at'
        ];
    }

    /**
     * @return Builder
     */
    public function query()
    {
        $approvedVersion = DB::table('organisation_versions')
            ->select(DB::raw('MAX(id) as id, organisation_id as oid'))
            ->groupBy('oid');

        $query = DB::table('filter_records')
            ->select([
                'filter_records.id',
                'filter_records.user_id',
                'filter_records.organisation_id',
                'filter_records.type as search_type',
                'organisation_versions.category',
                'organisation_versions.country',
                'organisation_versions.state',
                'filter_records.land_types',
                'filter_records.land_ownerships',
                'filter_records.land_size',
                'filter_records.land_continent',
                'filter_records.land_country',
                'filter_records.restoration_methods',
                'filter_records.restoration_goals',
                'filter_records.funding_sources',
                'filter_records.funding_amount',
                'filter_records.long_term_engagement',
                'filter_records.reporting_frequency',
                'filter_records.reporting_level',
                'filter_records.sustainable_development_goals',
                'filter_records.price_per_tree',
                'filter_records.created_at'
            ])
            ->join('organisation_versions', 'organisation_versions.organisation_id', '=', 'filter_records.organisation_id')
            ->joinSub($approvedVersion, 'organisations', function ($join) {
                $join->on('organisation_versions.id', '=', 'organisations.id');
            })
            ->orderBy('filter_records.id');

        return $query;
    }
}
