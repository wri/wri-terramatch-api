<?php

namespace App\Exports;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * This class finds:
 *     users
 * which are:
 *     verified
 *     belonging to organisations which are:
 *         approved
 *     created in the last month
 */
class UsersExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'user_id',
            'user_first_name',
            'user_last_name',
            'organisation_id',
            'organisation_name',
            'organisation_category',
            'organisation_country',
            'user_last_logged_in_at',
            'user_created_at',
        ];
    }

    public function collection(): Collection
    {
        $query = DB::table('users')
            ->select([
                'users.id AS user_id',
                'users.first_name AS user_first_name',
                'users.last_name AS user_last_name',
                'organisation_versions.organisation_id AS organisation_id',
                'organisation_versions.name AS organisation_name',
                'organisation_versions.category AS organisation_category',
                'organisation_versions.country AS organisation_country',
                'users.last_logged_in_at AS user_last_logged_in_at',
                'users.created_at AS user_created_at',
            ])
            ->join('organisations', function (JoinClause $join) {
                $join->on('users.organisation_id', '=', 'organisations.id');
            })
            ->join('organisation_versions', function (JoinClause $join) {
                $join->on('organisations.id', '=', 'organisation_versions.organisation_id')
                    ->where('organisation_versions.status', '=', 'approved');
            })
            ->where('role', '=', 'user')
            ->whereNotNull('password')
            ->whereNotNull('email_address_verified_at')
            ->orderBy('users.created_at');

        return $query->get();
    }
}
