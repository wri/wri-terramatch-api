<?php

namespace App\Console\Commands;

use App\Models\V2\I18n\I18nItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class V2GenerateTranslationFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-translation-file-generate {--testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a file for use with transifex';

    public function handle()
    {
        $i18nItems = I18nItem::select(['short_value','long_value', 'hash'])
                ->whereIn('status', [
                I18nItem::STATUS_DRAFT,
                I18nItem::STATUS_MODIFIED,
            ])
            ->groupBy('hash', 'short_value', 'long_value')
            ->get();
        $length = $i18nItems->count();
        $this->info('Generating list of translations');
        $progress = $this->output->createProgressBar($length);
        $list = [];
        foreach ($i18nItems as $i18nItem) {
            $list[$i18nItem->hash] = ['string' => $i18nItem->value];
            $progress->advance();
        }
        $progress->finish();

        Storage::disk('translations')->put('RequiredTranslations.json', json_encode($list));
        $path = Storage::disk('translations')->path('RequiredTranslations.json');
        $token = config('settings.transifex_token');
        $secret = config('settings.transifex_secret');

        $args = [
            'txjs-cli push',
            $path,
            '--parser=txnativejson',
            "--token='$token'",
            "--secret='$secret'",
            "--append-tags='custom-form'",
        ];

        $command = implode(' ', $args);

        if (! $this->option('testing')) {
            exec($command);
        }

        $this->info('Updating status of translations');
        $progress = $this->output->createProgressBar($length);
        foreach ($i18nItems as $i18nItem) {
            $i18nItem->update(['status' => I18nItem::STATUS_PENDING]);
            $progress->advance();
        }
        $progress->finish();
    }
}
