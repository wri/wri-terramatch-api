<?php

namespace App\Jobs;

use App\Http\Middleware\SetAuthenticatedUserForJob;
use App\Mail\PolygonOperationsComplete;
use App\Models\DelayedJob;
use App\Models\DelayedJobProgress;
use App\Models\V2\Sites\Site;
use App\Services\PolygonService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class FixPolygonOverlapJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;

    protected $polygonService;

    protected $polygonUuids;

    public $authUserId;

    protected $delayed_job_id;

    /**
     * Create a new job instance.
     *
     * @param array $polygonUuids
     */
    public function __construct(string $delayed_job_id, array $polygonUuids, int $authUserId)
    {
        $this->polygonUuids = $polygonUuids;
        $this->authUserId = $authUserId;
        $this->delayed_job_id = $delayed_job_id;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new SetAuthenticatedUserForJob()];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        try {
            $delayedJob = DelayedJobProgress::findOrFail($this->delayed_job_id);
            $user = Auth::user();
            $metadata = $delayedJob->metadata;
            $entityId = $metadata['entity_id'] ?? null;
            $site = Site::findOrFail($entityId);
            $userForMail = $delayedJob->creator;
            if ($user) {
                $polygonsClipped = App::make(PolygonService::class)->processClippedPolygons($this->polygonUuids, $this->delayed_job_id);
                $delayedJob->update([
                  'status' => DelayedJobProgress::STATUS_SUCCEEDED,
                  'payload' => json_encode(['updated_polygons' => $polygonsClipped]),
                  'status_code' => Response::HTTP_OK,
                  'progress' => 100,
                ]);

                Mail::to($user->email_address)
                ->send(new PolygonOperationsComplete(
                    $site,
                    'Fix',
                    $userForMail,
                    now()
                ));
            }
        } catch (Exception $e) {
            Log::error('Error in Fix Polygon Overlap Job: ' . $e->getMessage());

            DelayedJob::where('uuid', $this->delayed_job_id)->update([
                'status' => DelayedJob::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        } catch (Throwable $e) {
            Log::error('Throwable Error in Fix overlap job: ' . $e->getMessage());

            DelayedJob::where('uuid', $this->delayed_job_id)->update([
                'status' => DelayedJob::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }
}
