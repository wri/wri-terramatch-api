<?php

namespace Database\Seeders;

use App\Models\V2\LocalizationKey;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddLocalizationKeys extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LocalizationKey::create([
            'key' => 'application-submitted-confirmation.subject',
            'value' => 'Welcome to our platform!',
        ]);

        LocalizationKey::create([
            'key' => 'application-submitted-confirmation.title',
            'value' => 'Your report is ready!',
        ]);

        LocalizationKey::create([
            'key' => 'application-submitted-confirmation.body',
            'value' => 'Your report is ready! Click the link below to download it.',
        ]);

    }
}
