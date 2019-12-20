<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\UpdatePricePerTreeJob;

/*
 * This class also acts as a service seeder. Some of the data we seed the database with relies on data being present in
 * S3, SQS, or SNS. You can't use the seeded data reliably without seeding those services, so it makes sense to seed
 * them at the same time. This also guarantees our tests use data which references real objects etc.
 */
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // environment
        if (!in_array(Config::get("app.env"), ["local", "testing", "pipelines"])) {
            throw new Exception();
        }
        // s3
        $s3Client = App::make("CustomS3Client");
        $objects = $s3Client->listObjects([
            "Bucket" => Config::get("app.s3.bucket")
        ]);
        $objects = $objects->toArray();
        if (array_key_exists("Contents", $objects)) {
            if (count($objects["Contents"]) > 0) {
                foreach ($objects["Contents"] as $object) {
                    $s3Client->deleteObject([
                        "Bucket" => Config::get("app.s3.bucket"),
                        "Key" => $object["Key"]
                    ]);
                }
            }
        }
        //sqs
        $sqsClient = App::make("CustomSqsClient");
        $queueUrl = $sqsClient->getQueueUrl(["QueueName" => Config::get("queue.connections.sqs.queue")]);
        $sqsClient->purgeQueue([
            "QueueUrl" => $queueUrl["QueueUrl"]
        ]);
        // sns
        $snsClient = App::make("CustomSnsClient");
        $endpoints = array_merge(
            $snsClient->listEndpointsByPlatformApplication([
                "PlatformApplicationArn" => Config::get("app.sns.android_arn")
            ])->get("Endpoints"),
            $snsClient->listEndpointsByPlatformApplication([
                "PlatformApplicationArn" => Config::get("app.sns.ios_arn")
            ])->get("Endpoints")
        );
        foreach ($endpoints as $endpoint) {
            $snsClient->deleteEndpoint(["EndpointArn" => $endpoint["EndpointArn"]]);
        }
        // database
        DB::statement("SET FOREIGN_KEY_CHECKS = 0;");
        foreach (DatabaseSeeder::getTables() as $table) {
            DB::statement("DELETE FROM " . $table . ";");
            DB::statement("ALTER TABLE " . $table . " AUTO_INCREMENT = 1;");
            $seeder = Str::ucfirst(Str::camel($table)) . "TableSeeder";
            $this->call($seeder);
        }
        DB::statement("SET FOREIGN_KEY_CHECKS = 1;");
        // jobs
        $pitches = DB::table("pitches")->get();
        foreach ($pitches as $pitch) {
            UpdatePricePerTreeJob::dispatchNow($pitch->id);
        }
    }
    
    public static function getTables(): array
    {
        $tables = [];
        $rows = DB::select("SHOW TABLES;");
        foreach ($rows as $row) {
            $property = "Tables_in_" . Config::get("database.connections.mysql.database");
            if (in_array($row->$property, ["cache", "migrations"])) {
                continue;
            }
            $tables[] = $row->$property;
        }
        return $tables;
    }

    public static function seedRandomObject(string $type): string
    {
        if ($type == "image") {
            $file = "image.png";
            $mimeType = "image/png";
            $extension = ".png";
        } else if ($type == "video") {
            $file = "video.mp4";
            $mimeType = "video/mp4";
            $extension = ".mp4";
        } else if ($type == "file") {
            $file = "file.pdf";
            $mimeType = "application/pdf";
            $extension = ".pdf";
        } else {
            throw new Exception();
        }
        $key = Str::random("32") . $extension;
        $contents = file_get_contents(__DIR__ . "/../../resources/seeds/" . $file);
        $s3Client = App::make("CustomS3Client");
        $s3Client->putObject([
            "Bucket" => Config::get("app.s3.bucket"),
            "Key" => $key,
            "Body" => $contents,
            "ACL" => "public-read",
            "ContentType" => $mimeType
        ]);
        return $key;
    }

    public static function setRawAttribute(Model $model, string $attribute, $value): void
    {
        $attributes = array_merge($model->toArray(), [$attribute => $value]);
        $model->setRawAttributes($attributes);
    }
}
