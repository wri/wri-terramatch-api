<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Config\Repository as Config;
use Illuminate\Support\Facades\App;

/**
 * This class offers a convenient wrapper for the AWS SDK S3 class. All the
 * validation and logic can be standardised by keeping it in one place.
 */
class FileService
{
    private $config = null;
    private $s3Client = null;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->s3Client = App::make("CustomS3Client");
    }

    public function create(string $pathname, string $mimeType): string
    {
        if (!file_exists($pathname) || !is_file($pathname) || !is_readable($pathname)) {
            throw new Exception();
        }
        if (!in_array($mimeType, ["image/jpeg", "image/png", "image/gif", "video/mp4", "application/pdf"])) {
            throw new Exception();
        }
        $extension = explode("/", $mimeType, 2)[1];
        if ($extension == "jpeg") {
            $extension = "jpg";
        }
        $key = uniqid(Str::random(32)) . "." . $extension;
        $this->s3Client->putObject([
            "Bucket" => $this->config->get("app.s3.bucket"),
            "Key" => $key,
            "Body" => file_get_contents($pathname),
            "ACL" => "public-read",
            "ContentType" => $mimeType
        ]);
        return $this->getPrefix() . "/" . $this->config->get("app.s3.bucket") . "/" . $key;
    }

    public function delete(string $pathname): void
    {
        $key = basename($pathname);
        $extension = explode(".", $key, 2)[1];
        if (!in_array($extension, ["jpg", "png", "gif", "mp4", "pdf"])) {
            throw new Exception();
        }
        $this->s3Client->deleteObject([
            "Bucket" => $this->config->get("app.s3.bucket"),
            "Key" => $key
        ]);
    }

    public function copy(string $pathname): string
    {
        $bucket = $this->config->get("app.s3.bucket");
        $key = basename($pathname);
        $extension = explode(".", $key, 2)[1];
        $copySource = $bucket . "/" . $key;
        $key = uniqid(Str::random(32)) . "." . $extension;
        $mimeTypes = [
            "jpg" => "image/jpeg",
            "png" => "image/png",
            "gif" => "image/gif",
            "mp4" => "video/mp4",
            "pdf" => "application/pdf"
        ];
        $mimeType = $mimeTypes[$extension];
        $this->s3Client->copyObject([
            "Bucket" => $bucket,
            "CopySource" => $copySource,
            "Key" => $key,
            "ACL" => "public-read",
            "ContentType" => $mimeType
        ]);

        return $this->getPrefix() . "/" . $this->config->get("app.s3.bucket") . "/" . $key;
    }

    /**
     * This method ensures that the object's URL is relative to the host and not
     * the container. If we use the result's ObjectURL directly it will have the
     * wrong domain when developing, thus we need to reconstruct it manually.
     */
    private function getPrefix(): string
    {
        if (in_array($this->config->get("app.env"), ["local", "testing", "pipelines"])) {
            return "http://127.0.0.1:9000";
        } else {
            return $this->config->get("app.s3.prefix");
        }
    }
}