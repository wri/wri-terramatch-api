<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CheckQueueLengthCommand extends Command
{
    protected $signature = 'check-queue-length';

    protected $description = 'Checks queue length and notifies Slack';

    public function handle(): int
    {
        $pendingQueueLength = Redis::command('llen', ['queues:wri']);

        if ($pendingQueueLength > 50) {
            Log::channel('slack')->critical(
                'The queue is currently at ' . $pendingQueueLength .
                ' on the ' . config('app.env') . ' environment' . PHP_EOL
            );
        }

        return 0;
    }
}
