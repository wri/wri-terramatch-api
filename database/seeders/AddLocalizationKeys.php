<?php

namespace Database\Seeders;

use App\Models\V2\LocalizationKey;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\V2\I18n\I18nTranslation;
use App\Helpers\I18nHelper;

class AddLocalizationKeys extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $localizationKeySubject = LocalizationKey::create([
            'key' => 'application-submitted-confirmation.subject',
            'value' => 'Welcome to our platform!',
        ]);

        $this->createItemAndTranslated($localizationKeySubject);

        $localizationKeyTitle = LocalizationKey::create([
            'key' => 'application-submitted-confirmation.title',
            'value' => 'Your report is ready!',
        ]);

        $this->createItemAndTranslated($localizationKeyTitle);

        $localizationKeyBody = LocalizationKey::create([
            'key' => 'application-submitted-confirmation.body',
            'value' => 'Your report is ready! Click the link below to download it.',
        ]);

        $this->createItemAndTranslated($localizationKeyBody);

    }

    public function createItemAndTranslated ($localizationKey) 
    {
        $localizationKey->value_id = I18nHelper::generateI18nItem($localizationKey, 'value');
        $localizationKey->save();

        // create translation to test
        $short = strlen($localizationKey->value) < 256;
        I18nTranslation::create([
            'i18n_item_id' => $localizationKey->value_id,
            'language' => 'en-US',
            'short_value' => $short ? $localizationKey->value : null,
            'long_value' => $short ? null : $localizationKey->value,
        ]);
    }
}
