<?php

namespace App\Console\Commands;

use App\Models\FilterRecord as FilterRecordModel;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;

class RemoveFilterRecordsCommand extends Command
{
    protected $signature = 'remove-filter-records';

    protected $description = 'Removes filter records older than 28 days';

    public function handle(): int
    {
        $past = new DateTime('now - 28 days', new DateTimeZone('UTC'));
        FilterRecordModel::where('created_at', '<=', $past)->delete();

        return 0;
    }
}
