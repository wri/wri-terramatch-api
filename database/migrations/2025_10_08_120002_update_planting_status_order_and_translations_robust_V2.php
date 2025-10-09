<?php

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update ENUM values on report tables with new order: replacement-planting before completed
        DB::statement("ALTER TABLE v2_site_reports MODIFY COLUMN planting_status ENUM('no-restoration-expected','not-started','in-progress','replacement-planting','completed') NULL");
        DB::statement("ALTER TABLE v2_project_reports MODIFY COLUMN planting_status ENUM('no-restoration-expected','not-started','in-progress','replacement-planting','completed') NULL");

        // Update I18n translations from kebab-case to sentence case
        $this->updatePlantingStatusTranslations();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert ENUM values to original order
        DB::statement("ALTER TABLE v2_site_reports MODIFY COLUMN planting_status ENUM('no-restoration-expected','not-started','in-progress','completed','replacement-planting') NULL");
        DB::statement("ALTER TABLE v2_project_reports MODIFY COLUMN planting_status ENUM('no-restoration-expected','not-started','in-progress','completed','replacement-planting') NULL");

        // Revert translations back to kebab-case
        $this->revertPlantingStatusTranslations();
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

    private function revertPlantingStatusTranslations(): void
    {
        $key = 'planting-status';
        $list = FormOptionList::where('key', $key)->first();

        if ($list) {
            // Delete existing options to recreate them in the original order
            $list->options()->delete();

            // Create options in the original order (completed before replacement-planting)
            $orderedOptions = [
                'no-restoration-expected',
                'not-started',
                'in-progress',
                'completed',
                'replacement-planting',
            ];

            foreach ($orderedOptions as $optionLabel) {
                $option = FormOptionListOption::create([
                    'form_option_list_id' => $list->id,
                    'label' => $optionLabel,
                    'slug' => \Illuminate\Support\Str::slug($optionLabel),
                ]);

                // Create I18n item with kebab-case (original format)
                $i18nItem = I18nItem::create([
                    'type' => 'short',
                    'status' => I18nItem::STATUS_DRAFT,
                    'short_value' => $optionLabel,
                ]);

                $option->label_id = $i18nItem->id;
                $option->save();
            }
        }
    }
};
