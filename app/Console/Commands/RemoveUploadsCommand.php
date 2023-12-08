<?php

namespace App\Console\Commands;

use App\Helpers\DraftHelper;
use App\Models\ElevatorVideo as ElevatorVideoModel;
use App\Models\Upload as UploadModel;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class RemoveUploadsCommand extends Command
{
    protected $signature = 'remove-uploads';

    protected $description = 'Removes uploads older than 1 day';

    public function handle(): int
    {
        $past = new DateTime('now - 1 day', new DateTimeZone('UTC'));
        $uploads = UploadModel::where('created_at', '<=', $past)
            ->whereNotIn('id', DraftHelper::findUploadsInDrafts())
            ->get();
        $fileService = App::make(\App\Services\FileService::class);
        foreach ($uploads as $upload) {
            ElevatorVideoModel::where('upload_id', '=', $upload->id)
                ->where('status', '=', 'finished')
                ->delete();
            $fileService->delete($upload->location);
            $upload->delete();
        }

        return 0;
    }
}
