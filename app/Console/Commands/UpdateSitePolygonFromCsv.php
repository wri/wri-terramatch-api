<?php

namespace App\Console\Commands;

use App\Models\V2\Sites\SitePolygon;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateSitePolygonFromCsv extends Command
{
    protected $signature = 'site-polygon:update-csv 
                            {csv_path : Path to the CSV file}
                            {attribute=plantstart : Attribute to update (default: plantstart)}';

    protected $description = 'Update SitePolygon fields from a CSV file based on UUIDs';

    public function handle()
    {
        $csvPath = $this->argument('csv_path');
        $attribute = $this->argument('attribute');

        if (! file_exists($csvPath)) {
            $this->error("CSV file not found at path: {$csvPath}");

            return Command::FAILURE;
        }

        $handle = fopen($csvPath, 'r');

        if (! $handle) {
            $this->error('Unable to open CSV file.');

            return Command::FAILURE;
        }

        $header = fgetcsv($handle);

        if (! $header) {
            $this->error('CSV file is empty or invalid.');
            fclose($handle);

            return Command::FAILURE;
        }

        $updated = 0;
        $notFound = 0;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);

                if (empty($data['uuid'])) {
                    $this->warn('Skipping row with empty UUID.');

                    continue;
                }

                $sitePolygon = SitePolygon::where('uuid', $data['uuid'])->first();

                if (! $sitePolygon) {
                    $this->warn("SitePolygon not found for UUID: {$data['uuid']}");
                    $notFound++;

                    continue;
                }

                if (! isset($data[$attribute])) {
                    $this->warn("Attribute '{$attribute}' not found in CSV row for UUID: {$data['uuid']}");

                    continue;
                }

                $value = $data[$attribute];

                if (in_array($attribute, ['plantstart', 'start_date'])) {
                    if (empty($value)) {
                        $value = null;
                    } else {
                        try {
                            $value = trim($value);

                            if (strpos($value, ' ') !== false) {
                                $value = explode(' ', $value)[0];
                            }

                            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                                $value = Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
                            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                                $value = Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
                            } else {
                                throw new \Exception('Unknown date format: ' . $value);
                            }
                        } catch (\Exception $e) {
                            $this->warn("Invalid date format for UUID {$data['uuid']}: {$value}");
                            $value = null;
                        }
                    }
                }


                $sitePolygon->{$attribute} = $value;
                $sitePolygon->save();

                $updated++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            $this->error('Error during update: ' . $e->getMessage());

            return Command::FAILURE;
        }

        fclose($handle);

        $this->info("Update complete: {$updated} rows updated. {$notFound} UUIDs not found.");

        return Command::SUCCESS;
    }
}
