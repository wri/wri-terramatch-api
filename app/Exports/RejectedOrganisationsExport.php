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
 *     organisations
 * which are:
 *     rejected
 *     created in the last month
 */
class RejectedOrganisationsExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'organisation_id',
            'organisation_name',
            'organisation_category',
            'organisation_country',
            'organisation_state',
            'organisation_city',
            'organisation_status',
            'organisation_rejected_reason',
            'organisation_created_at',
        ];
    }

    public function collection(): Collection
    {
        $query = DB::table('organisations')
            ->select([
                'organisations.id AS organisation_id',
                'organisation_versions.name AS organisation_name',
                'organisation_versions.category AS organisation_category',
                'organisation_versions.country AS organisation_country',
                'organisation_versions.state AS organisation_state',
                'organisation_versions.city AS organisation_city',
                'organisation_versions.status AS organisation_status',
                'organisation_versions.rejected_reason AS organisation_rejected_reason',
                'organisations.created_at AS organisation_created_at',
            ])
            ->join('organisation_versions', function (JoinClause $join) {
                $join->on('organisations.id', '=', 'organisation_versions.organisation_id')
                    ->orderByDesc('created_at');
            })
            ->where('organisation_versions.status', '=', 'rejected')
            ->whereDate('organisation_versions.created_at', '>=', Carbon::now()->subDays(28)->toDateString())
            ->orderBy('organisation_versions.created_at');

        return $query->get();
    }
}
