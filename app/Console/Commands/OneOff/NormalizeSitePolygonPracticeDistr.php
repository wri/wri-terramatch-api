<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Sites\SitePolygon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        $query = SitePolygon::withTrashed()->orderBy('id')->select('id', 'practice', 'distr');
        $errors = 0;
        $this->withProgressBar($limit > 0 ? $limit : (clone $query)->count(), function ($progressBar) use ($limit, $query, $dryRun, &$errors) {
            $total = 0;
            $query->chunkById(100, function ($rows) use ($limit, $progressBar, &$errors, $dryRun, &$total) {
                foreach ($rows as $row) {
                    if ($limit > 0 && ++$total > $limit) {
                        // query->limit() doesn't interact as you might expect with chunkById, so we have to track it
                        // ourselves.
                        $this->info("\n\nLimit reached; early exit");
                        exit();
                    }

                    $distr = $row->getRawOriginal('distr');
                    $practice = $row->getRawOriginal('practice');
                    if ($this->isArrayFormat($distr) && $this->isArrayFormat($practice)) {
                        // Make the script faster on re-runs by skipping rows that have already been updated.
                        $progressBar->advance();
                        continue;
                    }

                    [$normalizedPractice, $practiceError] = $this->normalizeList($practice, self::PRACTICE_SYNONYMS, self::PRACTICE_ALLOWED);
                    [$normalizedDistr, $distrError] = $this->normalizeList($distr, self::DISTR_SYNONYMS, self::DISTR_ALLOWED);

                    if ($practiceError || $distrError) {
                        $errors++;
                        $this->warn("Row id={$row->id} has unexpected value(s) [practice='{$row->practice}', distr='{$row->distr}']");
                        $progressBar->advance();
                        continue;
                    }

                    $updates = [];
                    $updates['practice'] = $normalizedPractice;
                    $updates['distr'] = $normalizedDistr;

                    if ($dryRun) {
                        $this->line("[DRY-RUN] id={$row->id} updates=" . json_encode($updates));
                    } else {
                        SitePolygon::withTrashed()->where('id', $row->id)->update($updates);
                    }

                    $progressBar->advance();
                }
            });
        });

        $this->info("Finished. errors=$errors" . ($dryRun ? ' [DRY-RUN]' : ''));

        return self::SUCCESS;
    }

    /**
     * Normalize a free-text multi-select field to a JSON array string of allowed canonical values.
     * Returns [array or null, changed:boolean, hadError:boolean]
     */
    private function normalizeList(?string $rawString, array $synonyms, array $allowed): array
    {
        if ($rawString === null || $rawString === '') {
            return [null, false];
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
            return [null, $hadError];
        }

        $json = array_values(array_keys($canonSet));
        return [$json, $hadError];
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

    private function isArrayFormat(?string $value) {
        return $value == null || Str::startsWith($value, "[") && Str::endsWith($value, "]");
    }
}
