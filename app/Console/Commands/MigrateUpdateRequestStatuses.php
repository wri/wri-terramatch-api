<?php

namespace App\Console\Commands;

use App\Models\V2\UpdateRequests\UpdateRequest;
use App\StateMachines\UpdateRequestStatusStateMachine;
use Illuminate\Console\Command;

class MigrateUpdateRequestStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-update-request-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates all UpdateRequests in database to match current status state machine values';

    private const VALID_STATUSES = [
        UpdateRequestStatusStateMachine::DRAFT,
        UpdateRequestStatusStateMachine::AWAITING_APPROVAL,
        UpdateRequestStatusStateMachine::NEEDS_MORE_INFORMATION,
        UpdateRequestStatusStateMachine::APPROVED,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $numClean = 0;
        $numErrors = 0;
        UpdateRequest::withoutTimestamps(function () use (&$numClean, &$numErrors) {
            UpdateRequest::withTrashed()->whereNotIn('status', self::VALID_STATUSES)->chunkById(
                100,
                function ($updateRequests) use (&$numClean, &$numErrors) {
                    foreach ($updateRequests as $updateRequest) {
                        if ($updateRequest->status == 'rejected') {
                            $this->handleRejected($updateRequest);
                            $numClean++;
                        } elseif ($updateRequest->status == 'requested') {
                            $this->handleRequested($updateRequest);
                            $numClean++;
                        } else {
                            echo "UpdateRequest $updateRequest->id has an unexpected status: $updateRequest->status\n";
                            $numErrors++;
                        }
                    }
                }
            );
        });

        echo "Migration completed. [UpdateRequests with errors: $numErrors, successful transitions: $numClean]";
    }

    private function handleRejected(UpdateRequest $updateRequest): void
    {
        // This body of work is removing the "rejected" status from UR. The current instances are getting moved
        // to needs-more-information
        $updateRequest->update(['status' => UpdateRequestStatusStateMachine::NEEDS_MORE_INFORMATION]);
    }

    private function handleRequested(UpdateRequest $updateRequest): void
    {
        // "requested" URs is a feature that is not currently in use. We're simple soft deleting them.
        // Moving the UR into "draft" status simply so it has a valid status in case that's necessary in the future
        $updateRequest->update(['status' => UpdateRequestStatusStateMachine::DRAFT]);
        $updateRequest->delete();
    }
}
