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
 *     interests
 * which are:
 *     not matched
 *     belonging to pitches which are:
 *         approved
 *         visible
 *     belonging to offers which are:
 *         visible
 *     created in the last month
 */
class InterestsExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'interest_id',
            'interest_initiator',
            'offer_id',
            'offer_name',
            'offer_funding_status',
            'offer_funding_status_updated_at',
            'offer_created_at',
            'pitch_id',
            'pitch_name',
            'pitch_funding_status',
            'pitch_funding_status_updated_at',
            'pitch_created_at',
            'interest_created_at',
        ];
    }

    public function collection(): Collection
    {
        $query = DB::table('interests')
            ->select([
                'interests.id AS interest_id',
                'interests.initiator AS interest_initiator',
                'interests.offer_id AS offer_id',
                'offers.name AS offer_name',
                'offers.visibility AS offer_funding_status',
                'offers.visibility_updated_at AS offer_funding_status_updated_at',
                'offers.created_at AS offer_created_at',
                'interests.pitch_id AS pitch_id',
                'pitch_versions.name AS pitch_name',
                'pitches.visibility AS pitch_funding_status',
                'pitches.visibility_updated_at AS pitch_funding_status_updated_at',
                'pitches.created_at AS pitch_created_at',
                'interests.created_at AS interest_created_at',
            ])
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
            ->where('interests.has_matched', '=', 0)
            ->whereNotIn('offers.visibility', ['archived', 'fully_invested_funded', 'finished'])
            ->whereNotIn('pitches.visibility', ['archived', 'fully_invested_funded', 'finished'])
            ->whereDate('interests.created_at', '>=', Carbon::now()->subDays(28)->toDateString())
            ->orderBy('interests.created_at');

        return $query->get();
    }
}
