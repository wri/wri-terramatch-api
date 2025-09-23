<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\FinancialIndicators;
use App\Models\V2\FundingProgramme;
use App\Models\V2\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateOrganisationFieldsToFinancialIndicators extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:migrate-organisation-fields-to-financial-indicators';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate organisation financial fields to financial indicators based on funding programme and organisation type';

    /**
     * Funding programme UUIDs to process
     *
     * @var array
     */
    private array $fundingProgrammeUuids = [
        '86b3ea32-8541-4525-b342-2d8010b3cdf7', // Harit Bharat Fund - Enterprises
        'a8a453a8-658c-48f3-ab79-cf23217bc8ed', // Harit Bharat Fund - Non-Profits
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting migration for organisation financial fields to financial indicators');

        $totalMigratedCount = 0;
        $totalErrorCount = 0;

        foreach ($this->fundingProgrammeUuids as $fundingProgrammeUuid) {
            $this->info("\n" . str_repeat('=', 60));
            $this->info("Processing funding programme: {$fundingProgrammeUuid}");

            // Validate funding programme exists
            $fundingProgramme = FundingProgramme::isUuid($fundingProgrammeUuid)->first();
            if (! $fundingProgramme) {
                $this->error("Funding programme with UUID {$fundingProgrammeUuid} not found");

                continue;
            }

            $this->info("Funding programme name: {$fundingProgramme->name}");

            // Get organisations data using the provided query
            $organisations = $this->getOrganisationsData($fundingProgrammeUuid);

            if ($organisations->isEmpty()) {
                $this->warn("No organisations found for funding programme {$fundingProgrammeUuid}");

                continue;
            }

            $this->info("Found {$organisations->count()} organisations to process");

            $migratedCount = 0;
            $errorCount = 0;

            foreach ($organisations as $orgData) {
                try {
                    $this->processOrganisation($orgData, $fundingProgramme);
                    $migratedCount++;
                } catch (\Exception $e) {
                    $this->error('Error processing organisation: ' . $e->getMessage());
                    Log::error('Migration error for organisation', [
                        'organisation_data' => $orgData,
                        'error' => $e->getMessage(),
                    ]);
                    $errorCount++;
                }
            }

            $this->info("Completed funding programme: {$fundingProgramme->name}");
            $this->info("Migrated: {$migratedCount}, Errors: {$errorCount}");

            $totalMigratedCount += $migratedCount;
            $totalErrorCount += $errorCount;
        }

        $this->info("\n" . str_repeat('=', 60));
        $this->info('Migration completed successfully!');
        $this->info("Total migrated: {$totalMigratedCount}, Total errors: {$totalErrorCount}");

        return 0;
    }

    /**
     * Get organisations data using the provided SQL query
     */
    private function getOrganisationsData(string $fundingProgrammeUuid)
    {
        $query = '
            SELECT 
                o.uuid,
                o.type as organisation_type,
                o.fin_budget_current_year, 
                o.fin_budget_1year, 
                o.fin_budget_2year, 
                o.fin_budget_3year, 
                o.organisation_revenue_this_year
            FROM organisations o
            JOIN applications a ON o.uuid = a.organisation_uuid
            WHERE a.funding_programme_uuid = ?
        ';

        return collect(DB::select($query, [$fundingProgrammeUuid]));
    }

    /**
     * Process individual organisation migration
     */
    private function processOrganisation($orgData, FundingProgramme $fundingProgramme): void
    {
        $organisation = Organisation::isUuid($orgData->uuid)->first();

        if (! $organisation) {
            throw new \Exception("Organisation with UUID {$orgData->uuid} not found");
        }

        $this->info("Processing organisation: {$organisation->name} (Type: {$orgData->organisation_type})");

        // Determine collection type based on organisation type
        $collection = $this->getCollectionType($orgData->organisation_type);

        // Define field mappings based on the image table
        $fieldMappings = $this->getFieldMappings($fundingProgramme->uuid, $orgData->organisation_type);

        foreach ($fieldMappings as $field => $year) {
            $amount = $orgData->$field ?? 0;

            if ($amount > 0) {
                $this->createFinancialIndicator($organisation->id, $year, $collection, $amount, $field);
            }
        }
    }

    /**
     * Determine collection type based on organisation type
     */
    private function getCollectionType(string $organisationType): string
    {
        return match ($organisationType) {
            'for-profit-organization' => FinancialIndicators::COLLECTION_REVENUE,
            'non-profit-organization', 'government-agency' => FinancialIndicators::COLLECTION_BUDGET,
            default => FinancialIndicators::COLLECTION_BUDGET,
        };
    }

    /**
     * Get field mappings based on funding programme and organisation type
     * Based on the mapping table in the image
     */
    private function getFieldMappings(string $fundingProgrammeUuid, string $organisationType): array
    {
        // Harit Bharat Fund - Enterprises
        if ($fundingProgrammeUuid === '86b3ea32-8541-4525-b342-2d8010b3cdf7') {
            if ($organisationType === 'for-profit-organization') {
                return [
                    'fin_budget_current_year' => date('Y'), // Current year
                    'fin_budget_1year' => 2022,
                    'fin_budget_2year' => 2021,
                    'fin_budget_3year' => 2020,
                    'organisation_revenue_this_year' => 2023,
                ];
            } elseif ($organisationType === 'non-profit-organization') {
                return [
                    'fin_budget_current_year' => date('Y'), // Current year
                    'fin_budget_1year' => 2022,
                    'fin_budget_2year' => 2021,
                    'fin_budget_3year' => 2020,
                    'organisation_revenue_this_year' => 2023,
                ];
            }
        }

        // Harit Bharat Fund - Non-Profits
        if ($fundingProgrammeUuid === 'a8a453a8-658c-48f3-ab79-cf23217bc8ed') {
            if ($organisationType === 'government-agency') {
                return [
                    'fin_budget_current_year' => 2023,
                    'fin_budget_1year' => 2022,
                    'fin_budget_2year' => 2021,
                    'fin_budget_3year' => 2020,
                    'organisation_revenue_this_year' => date('Y'), // Current year
                ];
            } elseif ($organisationType === 'non-profit-organization') {
                return [
                    'fin_budget_current_year' => 2023,
                    'fin_budget_1year' => 2022,
                    'fin_budget_2year' => 2021,
                    'fin_budget_3year' => 2020,
                    'organisation_revenue_this_year' => date('Y'), // Current year
                ];
            }
        }

        // Default mapping for other programmes
        return [
            'fin_budget_current_year' => date('Y'),
            'fin_budget_1year' => date('Y') - 1,
            'fin_budget_2year' => date('Y') - 2,
            'fin_budget_3year' => date('Y') - 3,
            'organisation_revenue_this_year' => date('Y'),
        ];
    }

    /**
     * Create or update financial indicator
     */
    private function createFinancialIndicator(int $organisationId, int $year, string $collection, float $amount, string $sourceField): void
    {
        $where = [
            'organisation_id' => $organisationId,
            'year' => $year,
            'collection' => $collection,
            'financial_report_id' => null,
        ];

        $existing = FinancialIndicators::where($where)->first();

        if ($existing) {
            $existing->update(['amount' => $amount]);
            $this->info("  Updated existing indicator: {$collection} for year {$year} = {$amount} (from {$sourceField})");
        } else {
            FinancialIndicators::create(array_merge($where, ['amount' => $amount]));
            $this->info("  Created new indicator: {$collection} for year {$year} = {$amount} (from {$sourceField})");
        }
    }
}
