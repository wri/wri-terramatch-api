<?php

namespace App\Console\Commands\OneOff;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeSitePolygonPracticeDistr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Example:
     *  php artisan one-off:normalize-site-polygon --dry-run
     *  php artisan one-off:normalize-site-polygon
     */
    protected $signature = 'one-off:normalize-site-polygon {--dry-run} {--limit=0}';

    /**
     * The console command description.
     */
    protected $description = 'Normalize site_polygon.practice and site_polygon.distr into standardized JSON array multi-select values.';

    private const PRACTICE_ALLOWED = [
        'assisted-natural-regeneration',
        'direct-seeding',
        'tree-planting',
    ];

    private const PRACTICE_SYNONYMS = [
        'anr' => 'assisted-natural-regeneration',
        'assisted natural regeneration' => 'assisted-natural-regeneration',
        'assisted-natural-regeneration' => 'assisted-natural-regeneration',
        'assisted_natural_regeneration' => 'assisted-natural-regeneration',

        'direct seeding' => 'direct-seeding',
        'direct-seeding' => 'direct-seeding',
        'direct_seeding' => 'direct-seeding',

        'tree planting' => 'tree-planting',
        'tree-planting' => 'tree-planting',
        'tree_planting' => 'tree-planting',
    ];

    private const DISTR_ALLOWED = [
        'full',
        'partial',
        'single-line',
    ];

    private const DISTR_SYNONYMS = [
        'full' => 'full',
        'partial' => 'partial',
        'single line' => 'single-line',
        'single-line' => 'single-line',
        'single_line' => 'single-line',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $this->info('Starting normalization of site_polygon.practice and site_polygon.distr' . ($dryRun ? ' [DRY-RUN]' : ''));

        $query = DB::table('site_polygon')
            ->select('id', 'practice', 'distr', 'deleted_at')
            ->whereNull('deleted_at')
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $total = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        $query->chunkById(1000, function ($rows) use (&$total, &$updated, &$skipped, &$errors, $dryRun) {
            foreach ($rows as $row) {
                $total++;

                [$normalizedPractice, $practiceChanged, $practiceError] = $this->normalizeList($row->practice, self::PRACTICE_SYNONYMS, self::PRACTICE_ALLOWED);
                [$normalizedDistr, $distrChanged, $distrError] = $this->normalizeList($row->distr, self::DISTR_SYNONYMS, self::DISTR_ALLOWED);

                if ($practiceError || $distrError) {
                    $errors++;
                    $this->warn("Row id={$row->id} has unexpected value(s) [practice='{$row->practice}', distr='{$row->distr}']");
                    // still proceed with what we could normalize
                }

                $shouldUpdate = false;
                $updates = [];

                if ($practiceChanged) {
                    $updates['practice'] = $normalizedPractice;
                    $shouldUpdate = true;
                }
                if ($distrChanged) {
                    $updates['distr'] = $normalizedDistr;
                    $shouldUpdate = true;
                }

                if ($shouldUpdate) {
                    if ($dryRun) {
                        $this->line("[DRY-RUN] id={$row->id} updates=" . json_encode($updates));
                    } else {
                        DB::table('site_polygon')->where('id', $row->id)->update($updates + ['updated_at' => DB::raw('NOW()')]);
                    }
                    $updated++;
                } else {
                    $skipped++;
                }
            }
        });

        $this->info("Finished. total=$total, updated=$updated, skipped=$skipped, errors=$errors" . ($dryRun ? ' [DRY-RUN]' : ''));

        return self::SUCCESS;
    }

    /**
     * Normalize a free-text multi-select field to a JSON array string of allowed canonical values.
     * Returns [jsonStringOrNull, changed:boolean, hadError:boolean]
     */
    private function normalizeList($raw, array $synonyms, array $allowed): array
    {
        if ($raw === null || $raw === '') {
            return [null, false, false];
        }

        $rawString = (string) $raw;

        // If already looks like a JSON array, try to validate and return as-is if valid
        $asJson = json_decode($rawString, true);
        if (is_array($asJson)) {
            $canon = [];
            foreach ($asJson as $item) {
                $mapped = $this->mapValue($item, $synonyms, $allowed);
                if ($mapped !== null) {
                    $canon[$mapped] = true;
                }
            }
            $result = json_encode(array_values(array_keys($canon)));

            return [$result, $result !== $rawString, false];
        }

        // Split by commas, trim, lowercase; also handle accidental semicolons
        $parts = preg_split('/[,;]/', $rawString) ?: [];
        $canonSet = [];
        $hadError = false;

        foreach ($parts as $part) {
            $value = trim(mb_strtolower($part));
            if ($value === '') {
                continue;
            }
            $mapped = $this->mapValue($value, $synonyms, $allowed);
            if ($mapped === null) {
                $hadError = true;

                continue;
            }
            $canonSet[$mapped] = true;
        }

        if (empty($canonSet)) {
            return [null, $rawString !== null, $hadError];
        }

        $json = json_encode(array_values(array_keys($canonSet)));

        return [$json, true, $hadError];
    }

    private function mapValue(string $value, array $synonyms, array $allowed): ?string
    {
        $normalized = $value;
        // remove duplicate whitespace and convert underscores to hyphens
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = str_replace('_', '-', $normalized);
        $normalized = trim($normalized);

        // exact allowed
        if (in_array($normalized, $allowed, true)) {
            return $normalized;
        }

        // map common space versions to hyphenated key for lookup
        $spacey = str_replace('-', ' ', $normalized);
        if (array_key_exists($spacey, $synonyms)) {
            return $synonyms[$spacey];
        }
        if (array_key_exists($normalized, $synonyms)) {
            return $synonyms[$normalized];
        }

        return null;
    }
}
