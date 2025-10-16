<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdatePlantingStatusOptionsOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oneoff:update-planting-status-options-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One-off command: Update planting status options order and frontend display text from kebab-case to sentence case';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting planting status options update...');

        // Update FormOptionListOption records
        $this->updatePlantingStatusTranslations();

        $this->info('Planting status options update completed successfully!');
    }

    /**
     * Update planting status translations from kebab-case to sentence case
     */
    private function updatePlantingStatusTranslations(): void
    {
        $this->info('Updating FormOptionListOption records...');

        $translations = [
            'no-restoration-expected' => 'No restoration expected',
            'not-started' => 'Not started',
            'in-progress' => 'In progress',
            'replacement-planting' => 'Replacement planting',
            'completed' => 'Completed',
        ];

        $key = 'planting-status';
        $list = FormOptionList::where('key', $key)->first();

        if (! $list) {
            $this->error("FormOptionList with key '{$key}' not found!");

            return;
        }

        $this->info("Found FormOptionList with ID: {$list->id}");

        // Delete existing options to recreate them in the correct order
        $deletedCount = $list->options()->count();
        $list->options()->delete();
        $this->info("Deleted {$deletedCount} existing planting status options");

        // Create options in the new order
        $orderedOptions = [
            'no-restoration-expected',
            'not-started',
            'in-progress',
            'replacement-planting',
            'completed',
        ];

        $createdCount = 0;
        foreach ($orderedOptions as $optionLabel) {
            try {
                $option = FormOptionListOption::create([
                    'form_option_list_id' => $list->id,
                    'label' => $translations[$optionLabel],
                    'slug' => Str::slug($optionLabel),
                ]);

                $i18nItem = I18nItem::create([
                    'type' => 'short',
                    'status' => I18nItem::STATUS_DRAFT,
                    'short_value' => $translations[$optionLabel],
                ]);

                $option->label_id = $i18nItem->id;
                $option->save();

                $this->info("✓ Created option: {$optionLabel} -> {$translations[$optionLabel]}");
                $createdCount++;
            } catch (\Exception $e) {
                $this->error("✗ Failed to create option '{$optionLabel}': " . $e->getMessage());
            }
        }

        $this->info("Successfully created {$createdCount} planting status options");
    }
}
