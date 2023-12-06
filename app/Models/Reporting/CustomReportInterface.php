<?php

namespace App\Models\Reporting;

interface CustomReportInterface
{
    public function setup(array $data,  ?object $exportable): void;

    public function availableFields(): array;

    public function generate(): array;

    public function stripKeys(array $data): array;
}
