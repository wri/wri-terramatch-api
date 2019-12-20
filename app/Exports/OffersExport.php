<?php

namespace App\Exports;

use App\Models\Offer;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class OffersExport implements FromQuery, WithHeadings
{
    use Exportable;

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'id', 'funding_amount', 'created_at'
        ];
    }


    /**
     * @return Builder
     */
    public function query()
    {
        return Offer::query()
            ->select('id', 'funding_amount', 'created_at')
            ->whereDate('created_at', '>', Carbon::now()->subDays(28)->toDateString());
    }
}
