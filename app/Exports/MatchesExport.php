<?php

namespace App\Exports;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * This class finds:
 *     matches
 * which are:
 *     belonging to offers which are:
 *         visible
 *     belonging to pitches which are:
 *         visible
 *         approved
 *     created in the last month
 */
class MatchesExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'match_id',
            'offer_id',
            'offer_name',
            'offer_funding_status',
            'offer_funding_status_updated_at',
            'offer_funding_amount',
            'offer_benefited_people',
            'offer_land_size',
            'offer_created_at',
            'pitch_id',
            'pitch_name',
            'pitch_funding_status',
            'pitch_funding_status_updated_at',
            'pitch_funding_amount',
            'pitch_benefited_people',
            'pitch_land_size',
            'pitch_created_at',
            'match_created_at',
        ];
    }

    public function collection(): Collection
    {
        $query = DB::table('matches')
            ->selectRaw('
                matches.id AS match_id,
                interests.offer_id AS offer_id,
                offers.name AS offer_name,
                offers.visibility AS offer_funding_status,
                offers.visibility_updated_at AS offer_funding_status_updated_at,
                offers.funding_amount AS offer_funding_amount,
                NULL AS offer_benefited_people,
                offers.land_size AS offer_land_size,
                offers.created_at AS offer_created_at,
                interests.pitch_id AS pitch_id,
                pitch_versions.name AS pitch_name,
                pitches.visibility AS pitch_funding_status,
                pitches.visibility_updated_at AS pitch_funding_status_updated_at,
                pitch_versions.funding_amount AS pitch_funding_amount,
                pitch_versions.benefited_people AS pitch_benefited_people,
                pitch_versions.land_size AS pitch_land_size,
                pitches.created_at AS pitch_created_at,
                matches.created_at AS match_created_at
            ')
            ->join('interests', function (JoinClause $join) {
                $join->on('matches.primary_interest_id', '=', 'interests.id');
            })
            ->join('offers', function (JoinClause $join) {
                $join->on('interests.offer_id', '=', 'offers.id');
            })
            ->join('pitches', function (JoinClause $join) {
                $join->on('interests.pitch_id', '=', 'pitches.id');
            })
            ->join('pitch_versions', function (JoinClause $join) {
                $join->on('pitches.id', '=', 'pitch_versions.pitch_id')
                    ->where('pitch_versions.status', '=', 'approved');
            })
            ->whereNotIn('offers.visibility', ['archived', 'finished'])
            ->whereNotIn('pitches.visibility', ['archived', 'finished'])
            ->whereDate('matches.created_at', '>=', Carbon::now()->subDays(28)->toDateString())
            ->orderBy('matches.created_at');

        return $query->get();
    }
}
