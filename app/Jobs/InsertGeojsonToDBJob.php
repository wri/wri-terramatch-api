<?php

namespace App\Jobs;

use App\Helpers\GeometryHelper;
use App\Models\DelayedJob;
use App\Services\PolygonService;
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
    public function handle()
    {
        try {
            DelayedJob::create([
              'uuid' => $this->job_uuid,
              'status' => self::STATUS_PENDING,
              'created_at' => now(),
            ]);
            $tempDir = sys_get_temp_dir();
            $geojsonPath = $tempDir . DIRECTORY_SEPARATOR . $this->geojsonFilename;
            $geojsonData = file_get_contents($geojsonPath);

            $service = App::make(PolygonService::class);

            if ($this->entity_type === 'project' || $this->entity_type === 'project-pitch') {
                $entity = $service->getEntity($this->entity_type, $this->entity_uuid);
                $hasBeenDeleted = GeometryHelper::deletePolygonWithRelated($entity);

                if ($entity && $hasBeenDeleted) {
                    $payload = $service->createProjectPolygon($entity, $geojsonData);
                } else {
                    Log::error('Entity not found');
                }
            } else {
                $geojson = json_decode($geojsonData, true);

                SitePolygonValidator::validate('FEATURE_BOUNDS', $geojson, false);
                SitePolygonValidator::validate('GEOMETRY_TYPE', $geojson, false);

                $payload = $service->createGeojsonModels($geojson, ['site_id' => $this->entity_uuid, 'source' => PolygonService::UPLOADED_SOURCE], $this->primary_uuid, $this->submit_polygon_loaded);
            }
            DelayedJob::where('uuid', $this->job_uuid)->update([
              'status' => self::STATUS_SUCCEEDED,
              'payload' => json_encode($payload),
              'updated_at' => now(),
            ]);
        } catch (Exception $e) {
            DelayedJob::where('uuid', $this->job_uuid)->update([
              'status' => self::STATUS_FAILED,
              'payload' => json_encode(['error' => $e->getMessage()]),
              'updated_at' => now(),
            ]);
            $errorMessage = $e->getMessage();
            Log::error('Error inserting GeoJSON to DB', ['error' => $errorMessage]);
        }
    }
}
