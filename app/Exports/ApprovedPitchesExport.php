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
 *     pitches
 * which are:
 *     approved
 *     visible
 *     belonging to organisations which are:
 *         approved
 *     created in the last month
 */
class ApprovedPitchesExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'pitch_id',
            'pitch_name',
            'organisation_id',
            'organisation_name',
            'organisation_category',
            'organisation_country',
            'pitch_status',
            'pitch_funding_status',
            'pitch_funding_status_updated_at',
            'pitch_created_at',
        ];
    }

    public function collection(): Collection
    {
        $query = DB::table('pitches')
            ->select([
                'pitches.id AS pitch_id',
                'pitch_versions.name AS pitch_name',
                'organisations.id AS organisation_id',
                'organisation_versions.name AS organisation_name',
                'organisation_versions.category AS organisation_category',
                'organisation_versions.country AS organisation_country',
                'pitch_versions.status AS pitch_status',
                'pitches.visibility AS pitch_funding_status',
                'pitches.visibility_updated_at AS pitch_funding_status_updated_at',
                'pitches.created_at AS pitch_created_at',
            ])
            ->join('pitch_versions', function (JoinClause $join) {
                $join->on('pitches.id', '=', 'pitch_versions.pitch_id')
                    ->orderByDesc('created_at');
            })
            ->join('organisations', function (JoinClause $join) {
                $join->on('pitches.organisation_id', '=', 'organisations.id');
            })
            ->join('organisation_versions', function (JoinClause $join) {
                $join->on('organisations.id', '=', 'organisation_versions.organisation_id')
                    ->where('organisation_versions.status', '=', 'approved');
            })
            ->where('pitch_versions.status', '=', 'approved')
            ->whereNotIn('pitches.visibility', ['archived', 'finished'])
            ->whereDate('pitch_versions.created_at', '>=', Carbon::now()->subDays(28)->toDateString())
            ->orderBy('pitch_versions.created_at');

        return $query->get();
    }
}
