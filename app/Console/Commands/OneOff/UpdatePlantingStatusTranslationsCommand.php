<?php

namespace App\Console\Commands\OneOff;

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
    protected $signature = 'one-off:update-planting-status-translations';

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
            // Get all existing options
            $options = $list->options;
            
            // Update labels to sentence case
            foreach ($options as $option) {
                if (isset($translations[$option->label])) {
                    $option->update([
                        'label' => $translations[$option->label]
                    ]);
                    $this->info("Updated option: {$option->label}");
                }
            }

            $this->info('Planting status labels updated to sentence case successfully!');
            $this->info('Note: The order is already correct with replacement-planting before completed.');
        } else {
            $this->error('Planting status option list not found!');
        }
    }
}
    