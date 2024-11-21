<?php

namespace App\Exports\V2;

use App\Models\V2\Organisation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrganisationsExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function collection(): Collection
    {
        ini_set('max_execution_time', 60);

        return  Organisation::query()->select([
            'uuid',
            'status',
            'type',
            'name',
            'phone',
            'hq_street_1',
            'hq_street_2',
            'hq_city',
            'hq_state',
            'hq_zipcode',
            'hq_country',
            'countries',
            'languages',
            'founding_date',
            'description',
            'web_url',
            'facebook_url',
            'instagram_url',
            'linkedin_url',
            'twitter_url',
            'fin_start_month',
            'fin_budget_3year',
            'fin_budget_2year',
            'fin_budget_1year',
            'fin_budget_current_year',
            'ha_restored_total',
            'ha_restored_3year',
            'trees_grown_total',
            'trees_grown_3year',
            'tree_care_approach',
            'relevant_experience_years',
            'updated_at',
            'created_at',
        ])->get();
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        $headings = [
            'uuid',
            'status',
            'type',
            'private',
            'name',
            'phone',
            'hq street 1',
            'hq street 2',
            'hq city',
            'hq state',
            'hq zipcode',
            'hq country',
            'countries',
            'languages',
            'founding date',
            'description',
            'tree species grown',
            'web url',
            'facebook url',
            'instagram url',
            'linkedin url',
            'twitter url',
            'fin start month',
            'fin budget 3year',
            'fin budget_2year',
            'fin budget 1year',
            'fin budget current_year',
            'ha restored total',
            'ha restored 3year',
            'trees grown total',
            'trees grown 3year',
            'tree care approach',
            'relevant experience years',
            'last updated at',
            'created at',
        ];

        return $this->addFileCollectionHeadings($headings);
    }

    public function map($organisation): array
    {
        $mapped = [
            $organisation->uuid,
            $organisation->readable_status,
            $organisation->readable_type,
            $organisation->private,
            $organisation->name,
            $organisation->phone,
            $organisation->hq_street_1,
            $organisation->hq_street_2,
            $organisation->hq_city,
            $organisation->hq_state,
            $organisation->hq_zipcode,
            $organisation->hq_country,
            $organisation->countries,
            $organisation->languages,
            $organisation->founding_date,
            $organisation->description,
            $this->buildTreeSpecies($organisation),
            $organisation->web_url,
            $organisation->facebook_url,
            $organisation->instagram_url,
            $organisation->linkedin_url,
            $organisation->twitter_url,
            $organisation->fin_start_month,
            $organisation->fin_budget_3year,
            $organisation->fin_budget_2year,
            $organisation->fin_budget_1year,
            $organisation->fin_budget_current_year,
            $organisation->ha_restored_total,
            $organisation->ha_restored_3year,
            $organisation->trees_grown_total,
            $organisation->trees_grown_3year,
            $organisation->tree_care_approach,
            $organisation->relevant_experience_years,
            $organisation->creaupdated_atted_at,
            $organisation->created_at,
        ];

        return $this->addFileCollectionValues($organisation, $mapped);
    }

    private function addFileCollectionValues(Organisation $organisation, array  $mapped): array
    {
        $organisation = Organisation::where('uuid', $organisation->uuid)->first();
        foreach ($organisation->fileConfiguration as $key => $config) {
            if ($config['multiple'] == true) {
                $medias = $organisation->getMedia($key);
                $list = [];
                foreach ($medias as $media) {
                    $list[] = $media->getFullUrl();
                }
                $mapped[] = '[' . implode(' ', $list) . ']';
            } else {
                $media = $organisation->getMedia($key)->first();
                if ($media) {
                    $mapped[] = $media->getFullUrl();
                } else {
                    $mapped[] = '';
                }
            }
        }

        return $mapped;
    }

    private function addFileCollectionHeadings(array $headings): array
    {
        foreach ((new Organisation())->getFileCollectionList() as $key => $value) {
            $headings[] = str_replace('_', ' ', $value);
        }

        return $headings;
    }

    private function buildTreeSpecies(Organisation $organisation): string
    {
        $list = [];
        $treeSpecies = $organisation->treeSpecies()->select('name', 'amount')->get();
        foreach ($treeSpecies as $treeSpecies) {
            $list[] = $treeSpecies->name . '(' . $treeSpecies->amount . ')';
        }

        return '[ ' . implode(',', $list) . ' ]';
    }
}
