<?php

namespace App\Http\Controllers\V2\FinancialReports;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\FinancialReports\ExportFinancialReportRequest;
use App\Models\V2\FinancialReport;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportFinancialReportController extends Controller
{
    public function __invoke(ExportFinancialReportRequest $exportFinancialReportRequest, ?string $financialReport = null): StreamedResponse
    {
        $header = [
            'ID', 'UUID', 'Organisation ID', 'Organisation Name', 'Status',
            'Year of Report', 'Currency', 'Financial Start Month', 'Submitted At',
            'Created At', 'Updated At',
            'Financial Indicators',
            'Funding Types',
        ];
        $records = [];

        // Si se proporciona un UUID específico, exportar solo ese reporte
        if ($financialReport) {
            $report = FinancialReport::where('uuid', $financialReport)->firstOrFail();
            $reports = collect([$report]);
        } else {
            // Exportar todos los reportes
            $reports = FinancialReport::with(['organisation.fundingTypes', 'financialCollection'])->get();
        }

        foreach ($reports as $report) {
            $financialIndicators = $report->financialCollection->map(function ($indicator) {
                return "{$indicator->collection}:{$indicator->amount}({$indicator->year})";
            })->implode('|');

            $fundingTypes = $report->organisation && $report->organisation->fundingTypes
                ? $report->organisation->fundingTypes->map(function ($ft) {
                    return "{$ft->type}:{$ft->amount}({$ft->year})";
                })->implode('|')
                : '';

            $records[] = [
                $report->id,
                $report->uuid,
                $report->organisation_id,
                $report->organisation->name ?? null,
                $report->status,
                $report->year_of_report,
                $report->currency,
                $report->fin_start_month,
                $report->submitted_at,
                $report->created_at,
                $report->updated_at,
                $financialIndicators,
                $fundingTypes,
            ];
        }

        $csv = Writer::createFromString();
        $csv->insertOne($header);
        $csv->insertAll($records);

        $filename = $financialReport 
            ? "Financial Report {$financialReport} - " . now() . '.csv'
            : 'Financial Reports Export - ' . now() . '.csv';

        return response()->streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
