<?php

namespace App\Models\Reporting;

use Ramsey\Uuid\Uuid;

abstract class CustomReport
{
    protected $exportable;

    protected $type;

    protected $format;

    protected $field_list;

    protected $duration;

    protected $date_from;

    protected $assets = [];

    protected $generatedData;

    protected $uuid;

    protected $tempDir;

    public function setup(array $data,  ?object $exportable = null): void
    {
        $this->uuid = Uuid::uuid4();
        $this->exportable = $exportable;
        $this->type = data_get($data, 'exportable_type', null);
        $this->format = data_get($data, 'format', null);
        $this->field_list = data_get($data, 'field_list', []);
        $this->duration = data_get($data, 'duration', null);
        $this->calculateFrom();
        $this->tempDir = public_path('storage/temp');
    }

    protected function mapHeaders(): array
    {
        $headings = [];
        $availableFields = $this->availableFields('exclude');
        foreach ($this->field_list as $headerLabel) {
            if (isset($availableFields[$headerLabel])) {
                $headings[$headerLabel] = $availableFields[$headerLabel];
            }
        }

        return $headings;
    }

    protected function calculateFrom()
    {
        if ($this->duration) {
            $this->date_from = now()->subMonths($this->duration);
        }
    }

    public function stripKeys(array $data): array
    {
        $stripped = [];
        foreach ($data as $row) {
            $stripped[] = array_values($row);
        }

        return $stripped;
    }

    protected function addAssetFile(string $path, string $name)
    {
        $this->assets[$path] = $name;
    }

    public function zipAndServe(): string
    {
        $this->checkTempDirectoryExists();
        $zipFilename = $this->tempDir . "/Custom Export ( $this->type )" . now() . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipFilename, \ZipArchive::CREATE);

        foreach ($this->assets as $name => $fileUrl) {
            $content = file_get_contents($fileUrl);
            $zip->addFromString($name, $content);
            //            $zip->addFile($fileUrl, $name);
        }

        //        $zip->addFile($fileUrl, $name);

        $zip->close();

        return  $zipFilename;
    }

    public function generateCSV($data, $name, $delimiter = ',', $enclosure = '"', $escape_char = '\\'): void
    {
        $data = $this->stripKeys($data);
        $this->checkTempDirectoryExists();
        $storagePath = $this->tempDir . '/custom_report_' . $this->uuid .'.csv';
        $f = fopen($storagePath, 'x+');

        foreach ($data as $item) {
            fputcsv($f, $item, $delimiter, $enclosure, $escape_char);
        }
        rewind($f);

        $this->addAssetFile($name, $storagePath);
    }

    private function checkTempDirectoryExists()
    {
        if (! file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0775, true);
        }
    }
}
