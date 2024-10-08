<?php

namespace App\Jobs;

use App\Models\DelayedJob;
use App\Services\PolygonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Throwable;
use Exception;

class FixPolygonOverlapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private const STATUS_PENDING = 'pending';
    private const STATUS_FAILED = 'failed';
    private const STATUS_SUCCEEDED = 'succeeded';


    protected $polygonService;
    protected $polygonUuids;
    protected $job_uuid;

    /**
     * Create a new job instance.
     *
     * @param array $polygonUuids
     */
    public function __construct(array $polygonUuids)
    {
        $this->polygonUuids = $polygonUuids;
        $this->job_uuid = Str::uuid()->toString();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
          DelayedJob::create([
            'uuid' => $this->job_uuid,
            'status' => self::STATUS_PENDING,
            'created_at' => now(),
          ]);
            $polygonService = App::make(PolygonService::class);
            $updatedPolygons = $polygonService->processClippedPolygons($this->polygonUuids);
            DelayedJob::where('uuid', $this->job_uuid)->update([
              'status' => self::STATUS_SUCCEEDED,
              'payload' => json_encode(['updated_polygons' => $updatedPolygons]),
              'updated_at' => now(),
          ]);

        }  catch (Exception $e) {
          Log::error('Error in RunSitePolygonsValidationJob: ' . $e->getMessage());
          
          DelayedJob::where('uuid', $this->job_uuid)->update([
              'status' => self::STATUS_FAILED,
              'payload' => json_encode(['error' => $e->getMessage()]),
              'updated_at' => now(),
          ]);
      } catch (Throwable $e) {
          Log::error('Throwable Error in RunSitePolygonsValidationJob: ' . $e->getMessage());
          
          DelayedJob::where('uuid', $this->job_uuid)->update([
              'status' => self::STATUS_FAILED,
              'payload' => json_encode(['error' => $e->getMessage()]),
              'updated_at' => now(),
          ]);
      }
    }

    public function getJobUuid()
    {
        return $this->job_uuid;
    }
}
