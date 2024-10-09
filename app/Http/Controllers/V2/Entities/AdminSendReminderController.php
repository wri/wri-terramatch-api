<?php

namespace App\Http\Controllers\V2\Entities;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Reminder\SendReminderRequest;
use App\Jobs\V2\SendReportReminderEmailsJob;
use App\Models\V2\EntityModel;

class AdminSendReminderController extends controller
{
    public function __invoke(SendReminderRequest $request, EntityModel $entity)
    {
        $data = $request->validated();

        SendReportReminderEmailsJob::dispatch($entity, data_get($data, 'feedback'));

        return response()->json(['message' => 'Reminder sent successfully.']);
    }
}
