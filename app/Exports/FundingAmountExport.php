<?php

namespace App\Exports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class FundingAmountExport implements FromQuery, WithHeadings
{
    use Exportable;

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'offer_id', 'pitch_id','offer_funding_amount','pitch_funding_amount','created_at'
        ];
    }

    /**
     * @return Builder
     */
    public function query()
    {
        $query =  DB::table('matches')
            ->join('interests', 'matches.primary_interest_id', '=', 'interests.id')
            ->join('offers', 'interests.offer_id', '=', 'offers.id')
            ->join('pitch_versions', 'interests.pitch_id', '=', 'pitch_versions.pitch_id')
            ->where('pitch_versions.status', '=', 'approved')
            ->select(
                'interests.offer_id',
                'interests.pitch_id',
                'offers.funding_amount as offer_funding_amount',
                'pitch_versions.funding_amount as pitch_funding_amount',
                'matches.created_at',
            )
            ->whereDate('matches.created_at', '>', Carbon::now()->subDays(28)->toDateString())
            ->orderBy('interests.offer_id');

            return $query;
    }
}
