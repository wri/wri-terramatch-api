<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

/**
 * This class offers a convenient wrapper for the AWS SDK S3 class. All the
 * validation and logic can be standardised by keeping it in one place.
 */
class FileService
{
    public const EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'video/mp4' => 'mp4',
        'video/quicktime' => 'mov',
        'video/3gpp' => '3gp',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/pdf' => 'pdf',
        'image/tiff' => 'tiff',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-excel' => 'xls',
        'text/plain' => 'csv',
        'text/csv' => 'csv',
    ];
    public const MIME_TYPES = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'mp4' => 'video/mp4',
        'mov' => 'video/quicktime',
        '3gp' => 'video/3gpp',
        'pdf' => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'doc' => 'application/msword',
        'tiff' => 'image/tiff',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xls' => 'application/vnd.ms-excel',
        'csv' => 'text/plain',
        'csv-2' => 'text/csv',
    ];

    private $s3Client = null;

    public function __construct()
    {
        $this->s3Client = App::make('CustomS3Client');
    }

    public function create(string $pathname, string $mimeType): string
    {
        if (! file_exists($pathname) || ! is_file($pathname) || ! is_readable($pathname)) {
            throw new Exception();
        }
        if (! in_array($mimeType, FileService::MIME_TYPES)) {
            throw new Exception();
        }
        $extension = FileService::EXTENSIONS[$mimeType];
        $key = $this->formatDirectories(Str::random(64) . '.' . $extension);
        $this->s3Client->putObject([
            'Bucket' => config('app.s3.bucket'),
            'Key' => $key,
            'Body' => file_get_contents($pathname),
            'ACL' => 'public-read',
            'ContentType' => $mimeType,
        ]);

        return $this->formatUrl($key);
    }

    public function delete(string $url): void
    {
        $key = $this->formatDirectories(basename($url));
        $extension = explode_pop('.', $key);
        if (! in_array($extension, FileService::EXTENSIONS)) {
            throw new Exception();
        }
        $this->s3Client->deleteObject([
            'Bucket' => config('app.s3.bucket'),
            'Key' => $key,
        ]);
    }

    public function copy(string $url): string
    {
        $bucket = config('app.s3.bucket');
        $fromKey = $this->formatDirectories(basename($url));
        $extension = explode_pop('.', $fromKey);
        if (! in_array($extension, FileService::EXTENSIONS)) {
            throw new Exception();
        }
        $toKey = $this->formatDirectories(Str::random(64) . '.' . $extension);
        $mimeType = FileService::MIME_TYPES[$extension];
        $this->s3Client->copyObject([
            'Bucket' => $bucket,
            'CopySource' => $bucket . '/' . $fromKey,
            'Key' => $toKey,
            'ACL' => 'public-read',
            'ContentType' => $mimeType,
        ]);

        return $this->formatUrl($toKey);
    }

    public function clone(string $url): string
    {
        $bucket = config('app.s3.bucket');
        $fromKey = $this->formatDirectories(basename($url));
        $extension = explode_pop('.', $fromKey);
        if (! in_array($extension, FileService::EXTENSIONS)) {
            throw new Exception();
        }
        $pathname = '/tmp/' . Str::random(64) . '.' . $extension;
        $this->s3Client->getObject([
            'Bucket' => $bucket,
            'Key' => $fromKey,
            'SaveAs' => $pathname,
        ]);

        return $pathname;
    }

    /**
     * This method ensures that the object's URL is relative to the host and not
     * the container. If we use the result's ObjectURL directly it will have the
     * wrong domain when developing, thus we need to reconstruct it manually.
     */
    public function formatUrl(string $key): string
    {
        if (in_array(Config::get('app.env'), ['testing','local', 'pipelines' ])) {
            //            $prefix = 'http://127.0.0.1:9000';
            $prefix = 'http://minio:9000';
        } else {
            $prefix = config('app.s3.prefix');
        }

        return $prefix . '/' . config('app.s3.bucket') . '/' . $key;
    }

    /**
     * This method enforces the directory structure inside S3. This will make
     * it easier to browse the data inside the S3 bucket.
     */
    public function formatDirectories(String $filename): String
    {
        $primary = substr($filename, 0, 2);
        $secondary = substr($filename, 2, 2);

        return $primary . '/' . $secondary . '/' . $filename;
    }
}
