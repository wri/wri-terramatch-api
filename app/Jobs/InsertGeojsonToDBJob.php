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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
class InsertGeojsonToDBJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0;

    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SUCCEEDED = 'succeeded';

    protected $entity_uuid;
    protected $entity_type;
    protected $primary_uuid;
    protected $submit_polygon_loaded;
    protected $redis_key;
    protected $delayed_job_id;

    public function __construct(string $redis_key, string $delayed_job_id, ?string $entity_uuid = null, ?string $entity_type = null, ?string $primary_uuid = null, ?bool $submit_polygon_loaded = false)
    {
        $this->redis_key = $redis_key;
        $this->entity_uuid = $entity_uuid;
        $this->entity_type = $entity_type;
        $this->primary_uuid = $primary_uuid;
        $this->submit_polygon_loaded = $submit_polygon_loaded;
        $this->delayed_job_id = $delayed_job_id;
    }

    public function handle(PolygonService $service)
    {
        try {
            $delayedJob = DelayedJob::findOrFail($this->delayed_job_id);
            $geojsonContent = Redis::get($this->redis_key);
            if (!$geojsonContent) {
                Log::error('GeoJSON content not found in Redis for key: ' . $this->redis_key);
                Redis::del($this->redis_key);
                return;
            }
            $uuids = $service->insertGeojsonToDBFromContent(
                $geojsonContent,
                $this->entity_uuid,
                $this->entity_type,
                $this->primary_uuid,
                $this->submit_polygon_loaded
            );

            if (isset($uuids['error'])) {
                throw new \Exception($uuids['error'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            App::make(SiteService::class)->setSiteToRestorationInProgress($this->entity_uuid);
            Redis::del($this->redis_key);
            $delayedJob->update([
                'status' => self::STATUS_SUCCEEDED,
                'payload' => json_encode($uuids),
                'status_code' => Response::HTTP_OK,
            ]);

        } catch (Exception $e) {
            Log::error('Error in InsertGeojsonToDBJob: ' . $e->getMessage());
            DelayedJob::where('id', $this->delayed_job_id)->update([
                'status' => self::STATUS_FAILED,
                'payload' => ['error' => $e->getMessage()],
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
            Redis::del($this->redis_key);
        }
    }
}
