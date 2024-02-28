<?php

namespace App\StateMachines;

use App\Models\V2\ReportModel;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;

class UpdateRequestStatusStateMachine extends StateMachine
{
    public const DRAFT = 'draft';
    public const AWAITING_APPROVAL = 'awaiting-approval';
    public const NEEDS_MORE_INFORMATION = 'needs-more-information';
    public const APPROVED = 'approved';

    public function recordHistory(): bool
    {
        return true;
    }

    public function transitions(): array
    {
        return [
            self::DRAFT => [self::AWAITING_APPROVAL],
            self::AWAITING_APPROVAL => [self::APPROVED, self::NEEDS_MORE_INFORMATION],
            self::NEEDS_MORE_INFORMATION => [self::APPROVED],
        ];
    }

    public function defaultState(): ?string
    {
        return self::DRAFT;
    }

    public function afterTransitionHooks(): array
    {
        $updateTaskStatus = function (string $fromStatus, UpdateRequest $updateRequest) {
            $model = $updateRequest->updaterequestable;
            if (in_array('update_request_status', $model->getFillable())) {
                $model->update(['update_request_status' => $updateRequest->status]);
            }

            if ($model instanceof ReportModel) {
                $model->task->checkStatus();
            }
        };
        return [
            self::NEEDS_MORE_INFORMATION => [$updateTaskStatus],
            self::AWAITING_APPROVAL => [$updateTaskStatus],
            self::APPROVED => [$updateTaskStatus],
        ];
    }
}
