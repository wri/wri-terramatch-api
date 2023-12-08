<?php

namespace App\Exports;

use App\Helpers\CountryHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * This class finds:
 *     organisations
 * which are:
 *     approved
 * from:
 *     the beginning of time
 */
class OrganisationsExport implements FromCollection, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'organisation_id',
            'organisation_name',
            'organisation_category',
            'organisation_type',
            'organisation_city',
            'organisation_state',
            'organisation_country',
            'organisation_founded',
            'organisation_website',
            'organisation_status',
            'organisation_version_created_at',
            'organisation_original_created_at',
            'pitches_count',
            'offers_count',
        ];
    }

    public function collection(): Collection
    {
        $latestVersions = DB::table('organisation_versions')
        ->select(DB::raw('organisation_versions.*'))
        ->joinSub(
            DB::table('organisation_versions')
                ->select('organisation_id', DB::raw('MAX(id) as id'))->groupBy('organisation_id'),
            'latest_version',
            function ($join) {
                $join->on('organisation_versions.id', '=', 'latest_version.id');
            }
        );

        $query = DB::table('organisations')
            ->select([
                'organisations.id AS organisation_id',
                'latest_organisation_versions.name AS organisation_name',
                'latest_organisation_versions.category AS organisation_category',
                'latest_organisation_versions.type AS organisation_type',
                'latest_organisation_versions.city AS organisation_city',
                'latest_organisation_versions.state AS organisation_state',
                'latest_organisation_versions.country AS organisation_country',
                'latest_organisation_versions.founded_at AS organisation_founded',
                'latest_organisation_versions.website AS organisation_website',
                'latest_organisation_versions.status AS organisation_status',
                'latest_organisation_versions.created_at AS organisation_version_created_at',
                'organisations.created_at AS organisation_original_created_at',
                DB::raw('
                    (
                        SELECT COUNT(`organisation_id`)
                        FROM   `pitches`
                        WHERE  `organisation_id` = `organisations`.`id`
                    ) AS `pitches_count`
                '),
                DB::raw('
                    (
                        SELECT COUNT(`organisation_id`)
                        FROM   `offers`
                        WHERE  `organisation_id` = `organisations`.`id`
                    ) AS `offers_count`
                '),
            ])
            ->joinSub(
                $latestVersions,
                'latest_organisation_versions',
                function ($join) {
                    $join->on('organisations.id', '=', 'latest_organisation_versions.organisation_id');
                }
            )
            ->orderBy('latest_organisation_versions.created_at');
        $collection = $query->get();
        $collection = CountryHelper::codesToNames($collection, ['organisation_country']);

        return $collection->unique('organisation_id');
    }
}
