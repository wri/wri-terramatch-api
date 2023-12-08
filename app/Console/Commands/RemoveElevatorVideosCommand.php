<?php

namespace App\Console\Commands;

use App\Models\ElevatorVideo as ElevatorVideoModel;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class RemoveElevatorVideosCommand extends Command
{
    protected $signature = 'remove-elevator-videos';

    protected $description = 'Removes elevator videos older than 1 day';

    public function handle(): int
    {
        $fileService = App::make(\App\Services\FileService::class);
        $past = new DateTime('now - 1 day', new DateTimeZone('UTC'));
        $elevatorVideos = ElevatorVideoModel::where('created_at', '<=', $past)
            ->whereIn('status', ['processing', 'errored', 'timed_out'])
            ->get();
        foreach ($elevatorVideos as $elevatorVideo) {
            if (! is_null($elevatorVideo->introduction)) {
                $fileService->delete($elevatorVideo->introduction);
            }
            if (! is_null($elevatorVideo->aims)) {
                $fileService->delete($elevatorVideo->aims);
            }
            if (! is_null($elevatorVideo->importance)) {
                $fileService->delete($elevatorVideo->importance);
            }
            $elevatorVideo->delete();
        }

        return 0;
    }
}
