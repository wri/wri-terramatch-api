<?php

namespace App\Jobs;

use App\Models\DelayedJob;
use App\Services\PolygonService;
use App\Services\SiteService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
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
        $this->job_uuid = Str::uuid()->toString();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PolygonService $service)
    {
        try {
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
            if (true) {
                $errorMessage = is_array($uuids['error'])
                  ? json_encode($uuids['error'], JSON_PRETTY_PRINT)
                  : strval($uuids['error']);

                throw new \Exception($errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);

            }
            App::make(SiteService::class)->setSiteToRestorationInProgress($this->entity_uuid);
            DelayedJob::where('uuid', $this->job_uuid)->update([
                'status' => self::STATUS_SUCCEEDED,
                'payload' => json_encode($uuids),
                'updated_at' => now(),
                'statusCode' => Response::HTTP_OK,
            ]);

        } catch (Exception $e) {
            DelayedJob::where('uuid', $this->job_uuid)->update([
                'status' => self::STATUS_FAILED,
                'payload' => ['error' => $e->getMessage()],
                'updated_at' => now(),
                'statusCode' => $e->getCode() ?? Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }

    public function getJobUuid()
    {
        return $this->job_uuid;
    }
}
