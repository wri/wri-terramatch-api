<?php

namespace App\Exports;

use App\Helpers\CountryHelper;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * This class finds:
 *     offers
 * which are:
 *     visible
 *     belonging to organisations which are:
 *         approved
 * from:
 *     the beginning of time
 */
class OffersExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'organisation_id',
            'organisation_name',
            'organisation_category',
            'organisation_type',
            'organisation_country',
            'offer_id',
            'offer_name',
            'offer_land_size',
            'offer_land_continent',
            'offer_land_country',
            'offer_funding_amount',
            'offer_price_per_tree',
            'offer_reporting_frequency',
            'offer_reporting_level',
            'offer_sustainable_development_goals',
            'offer_land_ownerships',
            'offer_land_types',
            'offer_funding_sources',
            'offer_long_term_engagement',
            'offer_restoration_method',
            'offer_created_at',
            'offer_funding_bracket',
            'offer_visibility',
        ];
    }

    public function collection(): Collection
    {
        $query = DB::table('offers')
            ->select([
                'organisations.id AS organisation_id',
                'organisation_versions.name AS organisation_name',
                'organisation_versions.category AS organisation_category',
                'organisation_versions.type AS organisation_type',
                'organisation_versions.country AS organisation_country',
                'offers.id AS offer_id',
                'offers.name AS offer_name',
                'offers.land_size AS offer_land_size',
                'offers.land_continent AS offer_land_continent',
                'offers.land_country AS offer_land_country',
                'offers.funding_amount AS offer_funding_amount',
                'offers.price_per_tree AS offer_price_per_tree',
                'offers.reporting_frequency AS offer_reporting_frequency',
                'offers.reporting_level AS offer_reporting_level',
                'offers.sustainable_development_goals AS offer_sustainable_development_goals',
                'offers.land_ownerships AS offer_land_ownerships',
                'offers.land_types AS offer_land_types',
                'offers.funding_sources AS offer_funding_sources',
                'offers.long_term_engagement AS offer_long_term_engagement',
                'offers.restoration_methods AS offer_restoration_method',
                'offers.created_at AS offer_created_at',
                'offers.funding_bracket AS offer_funding_bracket',
                'offers.visibility AS offer_visibility',
            ])
            ->join('organisations', function (JoinClause $join) {
                $join->on('offers.organisation_id', '=', 'organisations.id');
            })
            ->join('organisation_versions', function (JoinClause $join) {
                $join->on('organisations.id', '=', 'organisation_versions.organisation_id')
                    ->where('organisation_versions.status', '=', 'approved');
            })
            ->whereNotIn('offers.visibility', ['archived', 'finished'])
            ->orderBy('offers.created_at');
        $collection = $query->get();
        $collection = CountryHelper::codesToNames($collection, ['organisation_country', 'offer_land_country']);

        return $collection->unique('offer_id');
    }
}
