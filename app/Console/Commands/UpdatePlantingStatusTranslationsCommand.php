<?php

namespace App\Console\Commands;

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Console\Command;

class UpdatePlantingStatusTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'planting-status:update-translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update planting status translations from kebab-case to sentence case';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating planting status translations...');
        
        $this->updatePlantingStatusTranslations();
        
        $this->info('Planting status translations updated successfully!');
    }

    private function updatePlantingStatusTranslations(): void
    {
        $translations = [
            'no-restoration-expected' => 'No restoration expected',
            'not-started' => 'Not started',
            'in-progress' => 'In progress',
            'replacement-planting' => 'Replacement planting',
            'completed' => 'Completed',
        ];

        $key = 'planting-status';
        $list = FormOptionList::where('key', $key)->first();

        if ($list) {
            // Delete existing options to recreate them in the correct order
            $list->options()->delete();

            // Create options in the new order
            $orderedOptions = [
                'no-restoration-expected',
                'not-started',
                'in-progress',
                'replacement-planting',
                'completed',
            ];

            foreach ($orderedOptions as $optionLabel) {
                $option = FormOptionListOption::create([
                    'form_option_list_id' => $list->id,
                    'label' => $optionLabel,
                    'slug' => \Illuminate\Support\Str::slug($optionLabel),
                ]);

                // Create I18n item with sentence case translation
                $i18nItem = I18nItem::create([
                    'type' => 'short',
                    'status' => I18nItem::STATUS_DRAFT,
                    'short_value' => $translations[$optionLabel],
                ]);

                $option->label_id = $i18nItem->id;
                $option->save();
            }
        }
    }
}
