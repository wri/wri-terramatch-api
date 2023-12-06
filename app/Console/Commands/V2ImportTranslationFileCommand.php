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
        $disk = Storage::disk('translations');

        if (! $disk->has('translated')) {
            $disk->makeDirectory('translated');
        }

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

        if (! $this->option('testing')) {
            exec($command);
        }

        $processedIds = [];

        foreach ($disk->files('translated') as $filename) {
            $withExt = substr($filename, strrpos($filename, '/') + 1);
            $lang = str_replace('_', '-', substr($withExt, 0, strrpos($withExt, '.')));
            $contents = json_decode($disk->get($filename));

            foreach ($contents as $hash => $translation) {
                $i18Items = I18nItem::where('hash', $hash)->get();
                $value = data_get($translation, 'string', '');
                $short = strlen($value) < 256;

                if (! empty($value)) {
                    foreach ($i18Items as $i18Item) {
                        $i18Translation = $i18Item->getTranslated($lang);

                        if (empty($i18Translation)) {
                            I18nTranslation::create([
                                'i18n_item_id' => $i18Item->id,
                                'language' => $lang,
                                'short_value' => $short ? $value : null,
                                'long_value' => $short ? null : $value,
                            ]);
                        } else {
                            $i18Translation->update([
                                'short_value' => $short ? $value : null,
                                'long_value' => $short ? null : $value,
                            ]);
                        }

                        if (! in_array($i18Item->id, $processedIds)) {
                            $processedIds[] = $i18Item->id;
                        }
                    }
                }
            }
        }

        I18nItem::whereIn('id', $processedIds)->update(['status' => I18nItem::STATUS_TRANSLATED]);
    }
}
