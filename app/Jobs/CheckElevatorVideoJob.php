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

class CheckElevatorVideoJob implements ShouldQueue
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
        $past = new DateTime('now - 2 minutes', new DateTimeZone('UTC'));
        if ($this->elevatorVideo->created_at->getTimestamp() < $past->getTimestamp()) {
            $this->elevatorVideo->status = 'timed_out';
            $this->elevatorVideo->saveOrFail();

            return;
        }
        $elasticTranscoderClient = App::make('CustomElasticTranscoderClient');

        try {
            $job = $elasticTranscoderClient->readJob(['Id' => $this->elevatorVideo->job_id]);
            $status = $job->get('Job')['Status'];
        } catch (Exception $exception) {
            Log::error($exception);
            $this->elevatorVideo->status = 'errored';
            $this->elevatorVideo->saveOrFail();

            return;
        }
        switch ($status) {
            case 'Complete':
                FinishElevatorVideoJob::dispatch($this->elevatorVideo);

                break;
            case 'Progressing':
                $delay = new DateTime('now + 5 seconds', new DateTimeZone('UTC'));
                CheckElevatorVideoJob::dispatch($this->elevatorVideo)->delay($delay);

                break;
            default:
                $this->elevatorVideo->status = 'errored';
                $this->elevatorVideo->saveOrFail();

                break;
        }
    }
}
