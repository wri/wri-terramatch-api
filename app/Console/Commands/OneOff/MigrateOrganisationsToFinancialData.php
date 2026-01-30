<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\FinancialIndicators;
use App\Models\V2\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateOrganisationsToFinancialData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:migrate-organisations-to-financial-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate organisations to financial data based on organisation type and project pitch or created_at date';

    /**
     * Mapping for organisations with project pitch data
     * Based on MAX(project_pitches.created_at) year
     */
    private function getYearMappingWithProjectPitch(string $year): array
    {
        return match ($year) {
            '2024' => [
                'fin_budget_current_year' => 2024,
                'fin_budget_1year' => 2023,
                'fin_budget_2year' => 2022,
                'fin_budget_3year' => 2021,
            ],
            '2023' => [
                'fin_budget_current_year' => 2023,
                'fin_budget_1year' => 2022,
                'fin_budget_2year' => 2021,
                'fin_budget_3year' => 2020,
            ],
            default => [],
        };
    }

    /**
     * Mapping for organisations without project pitch data
     * Based on organisations.created_at year
     */
    private function getYearMappingWithoutProjectPitch(string $year): array
    {
        return match ($year) {
            '2025' => [
                'fin_budget_current_year' => 2025,
                'fin_budget_1year' => 2024,
                'fin_budget_2year' => 2023,
                'fin_budget_3year' => 2022,
            ],
            '2024' => [
                'fin_budget_current_year' => 2024,
                'fin_budget_1year' => 2023,
                'fin_budget_2year' => 2022,
                'fin_budget_3year' => 2021,
            ],
            '2023' => [
                'fin_budget_current_year' => 2023,
                'fin_budget_1year' => 2022,
                'fin_budget_2year' => 2021,
                'fin_budget_3year' => 2020,
            ],
            '2022' => [
                'fin_budget_current_year' => 2022,
                'fin_budget_1year' => 2021,
                'fin_budget_2year' => 2020,
                'fin_budget_3year' => 2019,
            ],
            '2021' => [
                'fin_budget_current_year' => 2021,
                'fin_budget_1year' => 2020,
                'fin_budget_2year' => 2019,
                'fin_budget_3year' => 2018,
            ],
            '2020' => [
                'fin_budget_current_year' => 2020,
                'fin_budget_1year' => 2019,
                'fin_budget_2year' => 2018,
                'fin_budget_3year' => 2017,
            ],
            default => [],
        };
    }

    /**
     * Get collection type based on organisation type
     */
    private function getCollectionType(string $organisationType): string
    {
        return match ($organisationType) {
            'non-profit-organization' => 'budget',
            'for-profit-organization' => 'revenue',
            default => 'budget',
        };
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of organisations to financial data...');

        // Process organisations with project pitch data (using INNER JOIN as per requirements)
        $this->processOrganisationsWithProjectPitch();

        // Process organisations without project pitch data (using LEFT JOIN with NULL filter as per requirements)
        $this->processOrganisationsWithoutProjectPitch();

        $this->info('Migration completed!');
    }

    /**
     * Process organisations with project pitch data
     * Uses INNER JOIN as specified in requirements
     */
    private function processOrganisationsWithProjectPitch(): void
    {
        $this->info('Processing organisations with project pitch data...');

        // Query exactly as specified in requirements
        $query = '
            SELECT 
                o.created_at,
                o.updated_at,
                o.id,
                o.uuid,
                o.name,
                o.type,
                o.fin_budget_1year,
                o.fin_budget_2year,
                o.fin_budget_3year,
                o.fin_budget_current_year,
                p.uuid AS project_pitch_uuid,
                p.created_at AS project_pitch_created_at
            FROM organisations o
            LEFT JOIN financial_indicators fi 
                ON o.id = fi.organisation_id
            INNER JOIN (
                SELECT 
                    organisation_id,
                    MAX(created_at) AS latest_pitch_created_at
                FROM project_pitches
                GROUP BY organisation_id
            ) AS latest 
                ON latest.organisation_id = o.uuid
            INNER JOIN project_pitches p 
                ON p.organisation_id = latest.organisation_id 
                AND p.created_at = latest.latest_pitch_created_at
            WHERE fi.organisation_id IS NULL
              AND (
                o.fin_budget_1year > 0
                OR o.fin_budget_2year > 0
                OR o.fin_budget_3year > 0
                OR o.fin_budget_current_year > 0
              )
              AND o.deleted_at IS NULL
            ORDER BY p.created_at DESC, o.created_at DESC
        ';

        $organisations = DB::select($query);

        $this->info('Found ' . count($organisations) . ' organisations with project pitch data');

        foreach ($organisations as $orgData) {
            $this->processOrganisationWithProjectPitch($orgData);
        }
    }

    /**
     * Process organisations without project pitch data
     * Uses LEFT JOIN with NULL filter as specified in requirements
     */
    private function processOrganisationsWithoutProjectPitch(): void
    {
        $this->info('Processing organisations without project pitch data...');

        // Query for organisations without project pitch data
        $query = '
            SELECT 
                o.created_at,
                o.updated_at,
                o.id,
                o.uuid,
                o.name,
                o.type,
                o.fin_budget_1year,
                o.fin_budget_2year,
                o.fin_budget_3year,
                o.fin_budget_current_year
            FROM organisations o
            LEFT JOIN financial_indicators fi 
                ON o.id = fi.organisation_id
            LEFT JOIN (
                SELECT DISTINCT organisation_id
                FROM project_pitches
            ) AS pp
                ON pp.organisation_id = o.uuid
            WHERE fi.organisation_id IS NULL
              AND pp.organisation_id IS NULL
              AND (
                o.fin_budget_1year > 0
                OR o.fin_budget_2year > 0
                OR o.fin_budget_3year > 0
                OR o.fin_budget_current_year > 0
              )
              AND o.deleted_at IS NULL
            ORDER BY o.created_at DESC
        ';

        $organisations = DB::select($query);

        $this->info('Found ' . count($organisations) . ' organisations without project pitch data');

        foreach ($organisations as $orgData) {
            $this->processOrganisationWithoutProjectPitch($orgData);
        }
    }

    /**
     * Process a single organisation with project pitch data
     */
    private function processOrganisationWithProjectPitch($orgData): void
    {
        $organisation = Organisation::find($orgData->id);

        if (! $organisation) {
            $this->warn("Organisation with ID {$orgData->id} not found");

            return;
        }

        // Get the year from project_pitch created_at
        $pitchYear = date('Y', strtotime($orgData->project_pitch_created_at));
        $yearMapping = $this->getYearMappingWithProjectPitch($pitchYear);

        if (empty($yearMapping)) {
            $this->warn("No year mapping found for project pitch year: {$pitchYear} (Organisation ID: {$orgData->id})");

            return;
        }

        $collection = $this->getCollectionType($orgData->type);

        $this->info("Processing organisation {$organisation->name} (ID: {$orgData->id}, Type: {$orgData->type}, Pitch Year: {$pitchYear})");

        foreach ($yearMapping as $field => $year) {
            $value = $orgData->$field ?? 0;

            if ($value > 0) {
                $this->safeUpdateOrCreate($orgData->id, $year, $collection, $value);
            }
        }
    }

    /**
     * Process a single organisation without project pitch data
     */
    private function processOrganisationWithoutProjectPitch($orgData): void
    {
        $organisation = Organisation::find($orgData->id);

        if (! $organisation) {
            $this->warn("Organisation with ID {$orgData->id} not found");

            return;
        }

        // Get the year from organisation created_at
        $orgYear = date('Y', strtotime($orgData->created_at));
        $yearMapping = $this->getYearMappingWithoutProjectPitch($orgYear);

        if (empty($yearMapping)) {
            $this->warn("No year mapping found for organisation year: {$orgYear} (Organisation ID: {$orgData->id})");

            return;
        }

        $collection = $this->getCollectionType($orgData->type);

        $this->info("Processing organisation {$organisation->name} (ID: {$orgData->id}, Type: {$orgData->type}, Created Year: {$orgYear})");

        foreach ($yearMapping as $field => $year) {
            $value = $orgData->$field ?? 0;

            if ($value > 0) {
                $this->safeUpdateOrCreate($orgData->id, $year, $collection, $value);
            }
        }
    }

    /**
     * Create or update financial indicator
     */
    private function safeUpdateOrCreate(int $orgId, int $year, string $collection, float $amount): void
    {
        $where = [
            'organisation_id' => $orgId,
            'year' => $year,
            'collection' => $collection,
            'financial_report_id' => null,
        ];

        FinancialIndicators::updateOrCreate($where, ['amount' => $amount ?? 0]);

        Log::info("Updated organisation {$orgId} with year {$year}, collection {$collection}, amount {$amount}");
    }
}
