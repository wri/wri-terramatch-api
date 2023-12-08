<?php

namespace Database\Seeders;

use App\Jobs\UpdatePricePerTreeJob;
use App\Models\Pitch as PitchModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * This class also acts as a service seeder. Some of the data we seed the database with relies on data being present in
 * S3, or SNS. You can't use the seeded data reliably without seeding those services, so it makes sense to seed
 * them at the same time. This also guarantees our tests use data which references real objects etc.
 */
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // environment
        if (! in_array(config('app.env'), ['local', 'testing', 'pipelines'])) {
            throw new Exception();
        }
        // s3
        $s3Client = App::make('CustomS3Client');
        $objects = $s3Client->listObjects([
            'Bucket' => config('app.s3.bucket'),
        ]);
        $objects = $objects->toArray();
        if (array_key_exists('Contents', $objects)) {
            if (count($objects['Contents']) > 0) {
                foreach ($objects['Contents'] as $object) {
                    $s3Client->deleteObject([
                        'Bucket' => config('app.s3.bucket'),
                        'Key' => $object['Key'],
                    ]);
                }
            }
        }
        // sns
        $snsClient = App::make('CustomSnsClient');
        $endpoints = array_merge(
            $snsClient->listEndpointsByPlatformApplication([
                'PlatformApplicationArn' => config('app.sns.android_arn'),
            ])->get('Endpoints'),
            $snsClient->listEndpointsByPlatformApplication([
                'PlatformApplicationArn' => config('app.sns.ios_arn'),
            ])->get('Endpoints')
        );
        foreach ($endpoints as $endpoint) {
            $snsClient->deleteEndpoint(['EndpointArn' => $endpoint['EndpointArn']]);
        }
        // database
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        foreach (DatabaseSeeder::getTables() as $table) {
            DB::statement('DELETE FROM ' . $table . ';');
            DB::statement('ALTER TABLE ' . $table . ' AUTO_INCREMENT = 1;');
            $seeder = 'Database\\Seeders\\' . Str::ucfirst(Str::camel($table)) . 'TableSeeder';
            if (file_exists(base_path('/database/seeders/' . Str::ucfirst(Str::camel($table)) . 'TableSeeder.php'))) {
                $this->call($seeder);
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        // jobs
        $pitches = PitchModel::get();
        foreach ($pitches as $pitch) {
            UpdatePricePerTreeJob::dispatchSync($pitch);
        }
    }

    public static function getTables(): array
    {
        $tables = [];
        $rows = DB::select('SHOW TABLES;');
        foreach ($rows as $row) {
            $property = 'Tables_in_' . config('database.connections.mysql.database');
            if (in_array($row->$property, ['cache', 'migrations', 'sessions', 'failed_jobs', 'form_option_lists', 'form_option_list_options'])) {
                continue;
            }
            $tables[] = $row->$property;
        }

        return $tables;
    }

    public static function seedRandomObject(string $type): string
    {
        if ($type == 'image') {
            $pathname = __DIR__ . '/../../resources/seeds/image.png';
            $mimeType = 'image/png';
        } elseif ($type == 'video') {
            $pathname = __DIR__ . '/../../resources/seeds/video.mp4';
            $mimeType = 'video/mp4';
        } elseif ($type == 'file') {
            $pathname = __DIR__ . '/../../resources/seeds/file.pdf';
            $mimeType = 'application/pdf';
        } else {
            throw new Exception();
        }
        $fileService = App::make(\App\Services\FileService::class);

        return $fileService->create($pathname, $mimeType);
    }

    public static function setRawAttribute(Model $model, string $attribute, $value): void
    {
        $attributes = $model->getAttributes();
        $attributes[$attribute] = $value;
        $model->setRawAttributes($attributes);
    }
}
