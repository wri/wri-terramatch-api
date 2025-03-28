<?php

namespace App\Console\Commands;

use App\Models\V2\I18n\I18nItem;
use App\Models\V2\I18n\I18nTranslation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class V2ImportTranslationFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-translation-file-import {--testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and import a transifex file';

    public function handle()
    {
        $this->info('Starting import of translations');
        $disk = Storage::disk('translations');

        if (! $disk->has('translated')) {
            $disk->makeDirectory('translated');
        }

        $this->info('Fetching translations from transifex');
        $path = $disk->path('translated');
        $token = config('settings.transifex_token');
        $secret = config('settings.transifex_secret');

        $command = implode(' ', [
            'txjs-cli pull',
            "--token='$token'",
            "--secret='$secret'",
            "--folder='$path'",
            "--filter-tags='custom-form'",
        ]);

        $this->info('Executing command: ' . $command);
        if (! $this->option('testing')) {
            exec($command);
        }

        $this->info('Importing translations');
        $processedIds = [];

        foreach ($disk->files('translated') as $filename) {
            $this->info('Importing translations from ' . $filename);
            $withExt = substr($filename, strrpos($filename, '/') + 1);
            $lang = str_replace('_', '-', substr($withExt, 0, strrpos($withExt, '.')));
            $contents = json_decode($disk->get($filename), true);
            $length = count($contents);
            $this->info('Processing ' . $length . ' translations');
            $progress = $this->output->createProgressBar($length);
            $created = 0;
            $updated = 0;
            foreach ($contents as $hash => $translation) {
                $progress->advance();

                $i18nItems = I18nItem::where('hash', $hash)->get();
                $value = data_get($translation, 'string', '');
                $short = strlen($value) < 256;

                if (! empty($value)) {
                    foreach ($i18nItems as $i18nItem) {
                        $i18nTranslation = $i18nItem->getTranslated($lang);

                        if (empty($i18nTranslation)) {
                            I18nTranslation::create([
                                'i18n_item_id' => $i18nItem->id,
                                'language' => $lang,
                                'short_value' => $short ? $value : null,
                                'long_value' => $short ? null : $value,
                            ]);
                            $created++;
                        } else {
                            $i18nTranslation->update([
                                'short_value' => $short ? $value : null,
                                'long_value' => $short ? null : $value,
                            ]);
                            $updated++;
                        }

                        if (! in_array($i18nItem->id, $processedIds)) {
                            $processedIds[] = $i18nItem->id;
                        }
                    }
                }
            }
        }

        $this->info('Updating status of translated items');
        collect($processedIds)->chunk(100)->each(function ($chunk) {
            I18nItem::whereIn('id', $chunk->all())->update(['status' => I18nItem::STATUS_TRANSLATED]);
        });
        $this->info('Import complete');
        $this->info('Created ' . $created . ' translations');
        $this->info('Updated ' . $updated . ' translations');
    }
}
