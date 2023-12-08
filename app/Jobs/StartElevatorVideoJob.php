<?php

namespace App\Jobs;

use App\Models\ElevatorVideo as ElevatorVideoModel;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StartElevatorVideoJob implements ShouldQueue
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
        $inputs = [
            ['Key' => 'introduction.mp4'],
            ['Key' => $fileService->formatDirectories(basename($this->elevatorVideo->introduction))],
            ['Key' => 'aims.mp4'],
            ['Key' => $fileService->formatDirectories(basename($this->elevatorVideo->aims))],
            ['Key' => 'importance.mp4'],
            ['Key' => $fileService->formatDirectories(basename($this->elevatorVideo->importance))],
        ];
        $elasticTranscoderClient = App::make('CustomElasticTranscoderClient');

        try {
            $job = $elasticTranscoderClient->createJob([
                'PipelineId' => config('app.elastic_transcoder.pipeline_id'),
                'Inputs' => $inputs,
                'Output' => [
                    'Key' => $fileService->formatDirectories(Str::random(64) . '.mp4'),
                    'PresetId' => config('app.elastic_transcoder.preset_id'),
                ],
            ]);
            $jobId = $job->get('Job')['Id'];
        } catch (Exception $exception) {
            Log::error($exception);
            $this->elevatorVideo->status = 'errored';
            $this->elevatorVideo->saveOrFail();

            return;
        }
        $this->elevatorVideo->job_id = $jobId;
        $this->elevatorVideo->saveOrfail();
        $delay = new DateTime('now + 5 seconds', new DateTimeZone('UTC'));
        CheckElevatorVideoJob::dispatch($this->elevatorVideo)->delay($delay);
    }
}
