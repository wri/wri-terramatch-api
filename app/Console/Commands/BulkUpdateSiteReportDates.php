<?php

namespace App\Console\Commands;

use App\Models\V2\Sites\SiteReport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class BulkUpdateSiteReportDates extends Command
{
    protected $signature = 'sitereports:bulk-update-dates {file}';

    protected $description = 'Bulk update site report due dates from a CSV file';

    public function handle(): void
    {
        $filePath = $this->argument('file');
        if (! File::exists($filePath)) {
            $this->error("CSV file not found at {$filePath}");

            return;
        }

        $output = new ConsoleOutput();
        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('Processing: %current% [%bar%] %percent:3s%%');
        $progressBar->start();

        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) {
                continue;
            }
            $uuid = $row[1];
            $dueDateString = $row[2];
            $dueDate = $this->parseDateString($dueDateString);
            $siteReport = SiteReport::where('uuid', $uuid)->first();
            if ($siteReport) {
                $siteReport->update(['due_at' => $dueDate]);
            }

            $progressBar->advance();
        }

        fclose($handle);
        $progressBar->finish();
        $output->writeln("\nUpdate complete!");
    }

    protected function parseDateString(string $dateString): ?Carbon
    {
        $formats = [
            'n/j/Y H:i',
            'n/j/Y H:i:s',
            'Y-m-d H:i:s',
            'Y-m-d',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $dateString)->setTimezone('UTC');
            } catch (\Exception $e) {
                // Ignore the exception and try the next format
            }
        }

        return null;
    }
}
