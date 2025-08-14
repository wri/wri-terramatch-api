<?php

namespace App\Console\Commands;

use App\Models\V2\FundingProgramme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CloseFundoFloraFundingProgrammesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'close-fundo-flora-funding-programmes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close Fundo Flora funding programmes by setting their status to disabled';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to close Fundo Flora funding programmes...');

        // Fundo Flora funding programme UUIDs
        $fundoFloraUuids = [
            'e1bdd11c-aefe-4424-ad57-0e8c5ea1e2ab', // Non-profit funding programme
            '22cdd788-abab-4542-ab6f-d8999aacab9d', // Enterprise funding programme
        ];

        $closedCount = 0;
        $errors = [];

        foreach ($fundoFloraUuids as $uuid) {
            try {
                $fundingProgramme = FundingProgramme::where('uuid', $uuid)->first();

                if (! $fundingProgramme) {
                    $error = "Funding programme with UUID {$uuid} not found";
                    $this->error($error);
                    $errors[] = $error;

                    continue;
                }

                if ($fundingProgramme->status === FundingProgramme::STATUS_DISABLED) {
                    $this->warn("Funding programme {$fundingProgramme->name} (UUID: {$uuid}) is already disabled");

                    continue;
                }

                $previousStatus = $fundingProgramme->status;
                $fundingProgramme->update(['status' => FundingProgramme::STATUS_DISABLED]);

                $this->info("Successfully closed funding programme: {$fundingProgramme->name} (UUID: {$uuid}) - Status changed from '{$previousStatus}' to 'disabled'");

                Log::info('Fundo Flora funding programme closed', [
                    'uuid' => $uuid,
                    'name' => $fundingProgramme->name,
                    'previous_status' => $previousStatus,
                    'new_status' => FundingProgramme::STATUS_DISABLED,
                    'closed_at' => now(),
                ]);

                $closedCount++;
            } catch (\Exception $e) {
                $error = "Error closing funding programme with UUID {$uuid}: " . $e->getMessage();
                $this->error($error);
                $errors[] = $error;

                Log::error('Error closing Fundo Flora funding programme', [
                    'uuid' => $uuid,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // Summary
        $this->newLine();
        $this->info('=== Summary ===');
        $this->info("Successfully closed: {$closedCount} funding programme(s)");

        if (! empty($errors)) {
            $this->error('Errors encountered: ' . count($errors));
            foreach ($errors as $error) {
                $this->error("- {$error}");
            }
        }

        if ($closedCount > 0) {
            $this->info('Fundo Flora funding programmes have been successfully closed. Applicants will no longer be able to submit applications.');
        }

        return $closedCount > 0 ? 0 : 1;
    }
}
