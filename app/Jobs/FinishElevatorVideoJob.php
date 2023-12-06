<?php

namespace App\Jobs;

use App\Models\ElevatorVideo as ElevatorVideoModel;
use App\Models\Upload as UploadModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class FinishElevatorVideoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $elevatorVideo;

    public function __construct(ElevatorVideoModel $elevatorVideo)
    {
        $this->elevatorVideo = $elevatorVideo;
    }

    public function handle()
    {
        $fileService = App::make(\App\Services\FileService::class);
        $fileService->delete($this->elevatorVideo->introduction);
        $this->elevatorVideo->introduction = null;
        $fileService->delete($this->elevatorVideo->aims);
        $this->elevatorVideo->aims = null;
        $fileService->delete($this->elevatorVideo->importance);
        $this->elevatorVideo->importance = null;
        $this->elevatorVideo->saveOrFail();
        $elasticTranscoderClient = App::make('CustomElasticTranscoderClient');

        try {
            $job = $elasticTranscoderClient->readJob(['Id' => $this->elevatorVideo->job_id]);
            $output = $job->get('Job')['Output']['Key'];
        } catch (Exception $exception) {
            Log::error($exception);
            $this->elevatorVideo->status = 'errored';
            $this->elevatorVideo->saveOrFail();

            return;
        }
        /**
         * This section behaves differently when running in a Docker
         * environment. The Docker image we're using to mock AWS Elastic
         * Transcoder doesn't actually create the concatenated video, so we need
         * to do this manually. It's a bit clunky, but this is far better than
         * connecting to AWS from a local environment. It also means our tests
         * can run quickly!
         */
        if (in_array(config('app.env'), ['local', 'testing', 'pipelines'])) {
            $video = __DIR__ . '/../../resources/seeds/video.mp4';
            $location = $fileService->create($video, 'video/mp4');
        } else {
            $location = $fileService->copy($fileService->formatUrl($output));
            $fileService->delete($fileService->formatUrl($output));
        }
        /**
         * This section creates an upload model. The user can then use this
         * upload as if they uploaded it themselves. At this point the elevator
         * video is finished with and no longer used.
         */
        $upload = new UploadModel();
        $upload->user_id = $this->elevatorVideo->user_id;
        $upload->location = $location;
        $upload->saveOrFail();
        $upload->refresh();
        $this->elevatorVideo->upload_id = $upload->id;
        $this->elevatorVideo->status = 'finished';
        $this->elevatorVideo->saveOrFail();
    }
}
