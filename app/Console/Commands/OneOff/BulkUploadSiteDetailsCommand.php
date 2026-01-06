<?php

namespace App\Console\Commands\OneOff;

use App\Console\Commands\Traits\Abortable;
use App\Console\Commands\Traits\AbortException;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Stratas\Strata;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BulkUploadSiteDetailsCommand extends Command
{
    use Abortable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:bulk-upload-site-details {file} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One-off command: Bulk upload site details (description, history, planting_pattern, stratas) from CSV file for CI Colombia';

    protected array $headerOrder = [];

    private int $processedCount = 0;

    private int $successCount = 0;

    private array $errors = [];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->executeAbortableScript(function () {
            $fileInput = $this->argument('file');

            // If it's just a filename, assume it's in the imports folder
            // Otherwise, use the full path provided
            if (strpos($fileInput, '/') === false && strpos($fileInput, '\\') === false) {
                $filePath = base_path('imports/') . $fileInput;
            } else {
                $filePath = $fileInput;
            }

            $this->assert(file_exists($filePath), "File not found: $filePath");
            $this->assert(is_readable($filePath), "File is not readable: $filePath");

            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if ($fileExtension === 'csv') {
                $this->processCsvFile($filePath);
            } else {
                $this->abort('Unsupported file type. Use .csv file');
            }

            $this->displayResults();
        });
    }

    /**
     * Process CSV file
     */
    private function processCsvFile(string $filePath): void
    {
        $this->info("Processing CSV file: $filePath");

        $fileHandle = fopen($filePath, 'r');
        if (! $fileHandle) {
            $this->abort("Could not open CSV file: $filePath");
        }

        $headerRow = fgetcsv($fileHandle);
        if (! $headerRow) {
            fclose($fileHandle);
            $this->abort('CSV file is empty or has no header row');
        }

        $this->parseHeaders($headerRow);

        $data = [];
        while (($row = fgetcsv($fileHandle)) !== false) {
            $data[] = $row;
        }
        fclose($fileHandle);

        $this->processRows($data);
    }

    /**
     * Parse and validate headers
     */
    private function parseHeaders(array $headerRow): void
    {
        foreach ($headerRow as $header) {
            // Remove BOM and normalize
            $header = trim($header, "\xEF\xBB\xBF");
            $header = Str::snake(Str::replaceMatches('/[0-9]+/', fn ($matches) => "_$matches[0]", $header));
            $this->headerOrder[] = $header;
        }

        // Validate required columns exist
        $this->assert(
            in_array('site_uuid', $this->headerOrder) || in_array('site_name', $this->headerOrder),
            'No site identifier column found. Expected: site_uuid or site_name'
        );
        $this->assert(
            in_array('project_uuid', $this->headerOrder),
            'No project_uuid column found'
        );
    }

    /**
     * Get column value by column name
     */
    private function getColumnValue(array $row, string $columnName): ?string
    {
        $index = array_search($columnName, $this->headerOrder);
        if ($index !== false && isset($row[$index])) {
            $value = trim($row[$index]);

            return ! empty($value) ? $value : null;
        }

        return null;
    }

    /**
     * Process all rows
     */
    private function processRows(array $data): void
    {
        if ($this->option('dry-run')) {
            $this->info("\n=== DRY RUN MODE - No changes will be made ===\n");
        }

        DB::beginTransaction();

        try {
            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 because header is row 1, arrays are 0-indexed

                try {
                    $this->processRow($row, $rowNumber);
                    $this->processedCount++;
                } catch (AbortException $e) {
                    $this->errors[] = "Row $rowNumber: " . $e->getMessage();
                    $this->logException($e);
                } catch (\Exception $e) {
                    $this->errors[] = "Row $rowNumber: " . $e->getMessage();
                    $this->error("Row $rowNumber: " . $e->getMessage());
                }
            }

            if (! empty($this->errors) && ! $this->option('dry-run')) {
                DB::rollBack();
                $this->error("\nImport aborted due to errors. No changes were made.");
                exit(1);
            }

            if (! $this->option('dry-run')) {
                DB::commit();
            } else {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->abort('Error processing rows: ' . $e->getMessage());
        }
    }

    /**
     * Process a single row
     *
     * @throws AbortException
     */
    private function processRow(array $row, int $rowNumber): void
    {
        // Get site identifier
        $siteUuid = $this->getColumnValue($row, 'site_uuid');
        $siteName = $this->getColumnValue($row, 'site_name');

        // Get project UUID for validation
        $projectUuid = $this->getColumnValue($row, 'project_uuid');
        $this->assert(! empty($projectUuid), "project_uuid is required on row $rowNumber");

        // Find project
        $project = Project::where('uuid', $projectUuid)->first();
        $this->assert($project !== null, "Project not found: $projectUuid (row $rowNumber)");

        // Find site
        $site = null;
        if ($siteUuid) {
            $site = Site::where('uuid', $siteUuid)
                ->where('project_id', $project->id)
                ->first();
        } elseif ($siteName) {
            $site = Site::where('name', $siteName)
                ->where('project_id', $project->id)
                ->first();
        }

        if (! $site) {
            $identifier = $siteUuid ?: $siteName;
            $this->abort("Site not found. Identifier: $identifier, Project: $projectUuid (row $rowNumber)");
        }

        // Get field values from CSV (exact column names from the file)
        $description = $this->getColumnValue($row, 'description_site');
        $history = $this->getColumnValue($row, 'history_site');
        $plantingPattern = $this->getColumnValue($row, 'planting_pattern_site');
        $stratas = $this->getColumnValue($row, 'stratas_site');

        if ($this->option('dry-run')) {
            $this->info("Row $rowNumber: Would update site {$site->uuid} ({$site->name})");
            if ($description) {
                $this->line('  - Description: ' . substr($description, 0, 80) . '...');
            }
            if ($history) {
                $this->line('  - History: ' . substr($history, 0, 80) . '...');
            }
            if ($plantingPattern) {
                $this->line("  - Planting Pattern: $plantingPattern");
            }
            if ($stratas) {
                $this->line("  - Stratas: $stratas");
            }

            return;
        }

        // Update site fields
        $updateData = [];
        if (! empty($description)) {
            $updateData['description'] = $description;
        }
        if (! empty($history)) {
            $updateData['history'] = $history;
        }
        if (! empty($plantingPattern)) {
            $updateData['planting_pattern'] = $plantingPattern;
        }

        if (! empty($updateData)) {
            $site->update($updateData);
            $this->successCount++;
            $this->info("Row $rowNumber: Updated site {$site->uuid} ({$site->name})");
        }

        // Handle stratas
        if (! empty($stratas)) {
            $this->updateStratas($site, $stratas);
        }
    }

    /**
     * Update stratas for a site
     */
    private function updateStratas(Site $site, string $stratasData): void
    {
        $stratasData = trim($stratasData);
        if (empty($stratasData)) {
            return;
        }

        // Try to parse as JSON first
        $stratasArray = json_decode($stratasData, true);

        // If not JSON, try comma-separated
        if (json_last_error() !== JSON_ERROR_NONE) {
            $stratasArray = array_filter(
                array_map('trim', explode(',', $stratasData)),
                fn ($item) => ! empty($item)
            );
        }

        // If still not an array, treat as single description
        if (! is_array($stratasArray) || empty($stratasArray)) {
            if (! empty($stratasData)) {
                $stratasArray = [$stratasData];
            } else {
                return;
            }
        }

        // Delete existing stratas for this site
        $site->stratas()->delete();

        // Create new stratas
        foreach ($stratasArray as $strataData) {
            if (is_array($strataData)) {
                // If it's an array, expect structure like ['description' => '...', 'extent' => '...']
                $extent = isset($strataData['extent'])
                    ? (is_numeric($strataData['extent']) ? (int) $strataData['extent'] : null)
                    : null;

                // Validate extent is between 0-100 if provided
                if ($extent !== null && ($extent < 0 || $extent > 100)) {
                    $extent = null;
                }

                Strata::create([
                    'stratasable_type' => get_class($site),
                    'stratasable_id' => $site->id,
                    'description' => $strataData['description'] ?? $strataData['desc'] ?? '',
                    'extent' => $extent,
                    'hidden' => false,
                ]);
            } else {
                // If it's a string, use it as description
                $strataString = trim((string) $strataData);
                if (! empty($strataString)) {
                    Strata::create([
                        'stratasable_type' => get_class($site),
                        'stratasable_id' => $site->id,
                        'description' => $strataString,
                        'extent' => null,
                        'hidden' => false,
                    ]);
                }
            }
        }
    }

    /**
     * Display final results
     */
    private function displayResults(): void
    {
        $this->info("\n=== Import Summary ===");
        $this->info("Processed rows: {$this->processedCount}");
        $this->info("Successfully updated: {$this->successCount}");

        if (! empty($this->errors)) {
            $this->warn('Errors: ' . count($this->errors));
            foreach ($this->errors as $error) {
                $this->error("  - $error");
            }
        } else {
            $this->info('No errors encountered.');
        }
    }
}
