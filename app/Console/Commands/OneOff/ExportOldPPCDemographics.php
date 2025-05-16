<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Generator;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Facades\Excel;

class ExportOldPPCDemographics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:export-old-ppc-demographics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports the workdays_paid and workdays_volunteer data for PPC reports from before the new demographics system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Exporting Demographic report data to self_reported_workdays.xlsx');

        $sheets = [
            $this->exportProjectReports(),
            $this->exportSiteReports(),
        ];
        Excel::store(new class ($sheets) implements WithMultipleSheets {
            use Exportable;

            private mixed $sheets;

            public function __construct($sheets)
            {
                $this->sheets = $sheets;
            }

            public function sheets(): array
            {
                return $this->sheets;
            }
        }, 'self_reported_workdays.xlsx', 'local');
    }

    private function exportProjectReports(): Withtitle|WithHeadings|FromGenerator
    {
        return new class ($this->output) implements WithTitle, WithHeadings, FromGenerator {
            private OutputStyle $output;

            public function __construct($output)
            {
                $this->output = $output;
            }

            public function generator(): Generator
            {
                $query = ProjectReport::where('framework_key', 'ppc')->where('due_at', '<=', Demographic::DEMOGRAPHICS_COUNT_CUTOFF);
                $this->output->info('Exporting Project Reports...');
                $progressBar = $this->output->createProgressBar((clone $query)->count());
                foreach ($query->lazy(100) as $report) {
                    $attributes = $report->attributesToArray();
                    yield [
                        $report->id,
                        $report->uuid,
                        'https://terramatch.org/admin#/projectReport/' . $report->uuid . '/show',
                        $report->organisation->name,
                        $report->project->name,
                        $report->status,
                        $report->update_request_status,
                        $report->due_at->format('Y-m-d'),
                        $attributes['workdays_paid'],
                        $attributes['workdays_volunteer'],
                    ];
                    $progressBar->advance();
                }

                $progressBar->finish();
            }

            public function headings(): array
            {
                return [
                    'id',
                    'uuid',
                    'link_to_terramatch',
                    'organisation_name',
                    'project_name',
                    'status',
                    'update_request_status',
                    'due_date',
                    'workdays_paid',
                    'workdays_volunteer',
                ];
            }

            public function title(): string
            {
                return 'Project reports';
            }
        };
    }

    private function exportSiteReports(): WithTitle|WithHeadings|FromGenerator
    {
        return new class ($this->output) implements WithTitle, WithHeadings, FromGenerator {
            private OutputStyle $output;

            public function __construct($output)
            {
                $this->output = $output;
            }

            public function generator(): Generator
            {
                $query = SiteReport::where('framework_key', 'ppc')->where('due_at', '<=', Demographic::DEMOGRAPHICS_COUNT_CUTOFF);
                $this->output->info('Exporting Site Reports...');
                $progressBar = $this->output->createProgressBar((clone $query)->count());
                foreach ($query->lazy(100) as $report) {
                    $attributes = $report->attributesToArray();
                    yield [
                        $report->id,
                        $report->uuid,
                        'https://terramatch.org/admin#/siteReport/' . $report->uuid . '/show',
                        $report->site->project->organisation->name,
                        $report->site->project->name,
                        $report->status,
                        $report->update_request_status,
                        $report->due_at->format('Y-m-d'),
                        $report->site->ppc_external_id,
                        $report->site->name,
                        $attributes['workdays_paid'],
                        $attributes['workdays_volunteer'],
                    ];
                    $progressBar->advance();
                }

                $progressBar->finish();
            }

            public function headings(): array
            {
                return [
                    'id',
                    'uuid',
                    'link_to_terramatch',
                    'organisation_name',
                    'project_name',
                    'status',
                    'update_request_status',
                    'due_date',
                    'site_id',
                    'site_name',
                    'workdays_paid',
                    'workdays_volunteer',
                ];
            }

            public function title(): string
            {
                return 'Site reports';
            }
        };
    }
}
