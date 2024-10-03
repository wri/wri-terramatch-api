<?php

namespace App\Jobs;

use App\Helpers\GeometryHelper;
use App\Models\DelayedJob;
use App\Services\PolygonService;
use App\Services\SiteService;
use App\Validators\SitePolygonValidator;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InsertGeojsonToDBJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const STATUS_PENDING = 'pending';
    private const STATUS_FAILED = 'failed';
    private const STATUS_SUCCEEDED = 'succeeded';

    // Define the properties for the parameters
    protected $geojsonFilename;

    protected $entity_uuid;

    protected $entity_type;

    protected $primary_uuid;

    protected $submit_polygon_loaded;

    protected $job_uuid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $geojsonFilename, ?string $entity_uuid = null, ?string $entity_type = null, ?string $primary_uuid = null, ?bool $submit_polygon_loaded = false)
    {
        $this->geojsonFilename = $geojsonFilename;
        $this->entity_uuid = $entity_uuid;
        $this->entity_type = $entity_type;
        $this->primary_uuid = $primary_uuid;
        $this->submit_polygon_loaded = $submit_polygon_loaded;
        $this->job_uuid = Str::uuid();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PolygonService $service)
    {
        try {
            Log::info('starting the job to sleep zzz....', ['job_uuid' => $this->job_uuid]);
            DelayedJob::create([
                'uuid' => $this->job_uuid,
                'status' => self::STATUS_PENDING,
                'created_at' => now(),
            ]);
            $uuids = $service->insertGeojsonToDB(
                $this->geojsonFilename,
                $this->entity_uuid,
                $this->entity_type,
                $this->primary_uuid,
                $this->submit_polygon_loaded
            );
            if (isset($uuids['error'])) {
              throw new Exception($uuids['error']);
            }
            App::make(SiteService::class)->setSiteToRestorationInProgress($this->entity_uuid);
            DelayedJob::where('uuid', $this->job_uuid)->update([
                'status' => self::STATUS_SUCCEEDED,
                'payload' => json_encode($uuids),
                'updated_at' => now(),
            ]);
    
        } catch (Exception $e) {
            DelayedJob::where('uuid', $this->job_uuid)->update([
                'status' => self::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'updated_at' => now(),
            ]);
    
            Log::error('Error inserting GeoJSON to DB', ['error' => $e->getMessage()]);
        }
    }
    public function getJobUuid()
    {
        return $this->job_uuid;
    }

    
}
