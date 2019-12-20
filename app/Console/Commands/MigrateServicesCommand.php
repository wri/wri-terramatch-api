<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class MigrateServicesCommand extends Command
{
    protected $signature = "migrate-services";
    protected $description = "Migrates S3, SQS, SNS";

    public function handle(): void
    {
        if (!in_array(Config::get("app.env"), ["local", "testing", "pipelines"])) {
            throw new Exception();
        }
        $this->migrateS3();
        $this->migrateSqs();
        $this->migrateSns();
    }

    private function migrateS3(): void
    {
        echo "Migrating S3...\n";
        $s3Client = App::make("CustomS3Client");
        $name = Config::get("app.s3.bucket");
        $buckets = $s3Client->listBuckets();
        $bucketExists = false;
        foreach ($buckets["Buckets"] as $bucket) {
            if ($bucket["Name"] == $name) {
                $bucketExists = true;
                break;
            }
        }
        if (!$bucketExists) {
            $s3Client->createBucket([
                'Bucket' => $name,
                "ACL" => "public-read"
            ]);
            $s3Client->putBucketPolicy([
                'Bucket' => $name,
                "Policy" => '{"Version":"2012-10-17","Statement":[{"Sid":"AddPerm","Effect":"Allow","Principal":"*","Action":["s3:GetObject"],"Resource":["arn:aws:s3:::' . $name . '/*"]}]}',
            ]);
            echo "SUCCESS\n";
        } else {
            echo "SKIPPING\n";
        }
    }

    private function migrateSqs(): void
    {
        echo "Migrating SQS...\n";
        $sqsClient = App::make("CustomSqsClient");
        $name = Config::get("queue.connections.sqs.queue");
        try {
            $sqsClient->getQueueUrl(["QueueName" => $name]);
            $queueExists = true;
        } catch (Exception $exception) {
            $queueExists = false;
        }
        if (!$queueExists) {
            $sqsClient->createQueue(["QueueName" => $name]);
            echo "SUCCESS\n";
        } else {
            echo "SKIPPING\n";
        }
    }

    private function migrateSns(): void
    {
        echo "Migrating SNS...\n";
        $snsClient = App::make("CustomSnsClient");
        $response = $snsClient->listPlatformApplications();
        $androidExists = false;
        $iosExists = false;
        foreach ($response->get("PlatformApplications") as $platformApplication) {
            $name = explode_pop("/", $platformApplication["PlatformApplicationArn"]);
            if ($name == "wri_rm_android") {
                $androidExists = true;
            }
            if ($name == "wri_rm_ios") {
                $iosExists = true;
            }
        }
        if ($androidExists && $iosExists) {
            echo "SKIPPING\n";
        } else {
            if (!$androidExists) {
                $snsClient->createPlatformApplication([
                    "Attributes" => [
                        "PlatformCredential" => "foo"
                    ],
                    "Name" => "wri_rm_android",
                    "Platform" => "FCM"
                ]);
            }
            if (!$iosExists) {
                $snsClient->createPlatformApplication([
                    "Attributes" => [
                        "PlatformCredential" => "bar"
                    ],
                    "Name" => "wri_rm_ios",
                    "Platform" => "APNS"
                ]);
            }
            echo "SUCCESS\n";
        }
    }
}
