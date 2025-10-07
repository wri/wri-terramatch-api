<?php

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update ENUM values on report tables
        DB::statement("ALTER TABLE v2_site_reports MODIFY COLUMN planting_status ENUM('no-restoration-expected','not-started','in-progress','replacement-planting','completed') NULL");
        DB::statement("ALTER TABLE v2_project_reports MODIFY COLUMN planting_status ENUM('no-restoration-expected','not-started','in-progress','replacement-planting','completed') NULL");

        // Ensure 'replacement-planting' option exists in planting-status option list
        $key = 'planting-status';
        $list = FormOptionList::where('key', $key)->first();
        if ($list) {
            $exists = $list->options()->where(function ($q) {
                $q->where('slug', 'replacement-planting')->orWhere('label', 'replacement-planting');
            })->exists();

            if (! $exists) {
                $option = FormOptionListOption::create([
                    'form_option_list_id' => $list->id,
                    'label' => 'replacement-planting',
                    'slug' => Str::slug('replacement-planting'),
                ]);

                if (empty($option->label_id)) {
                    $option->label_id = $this->generateIfMissingI18nItem($option, 'label');
                    $option->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert ENUM values on report tables to previous set without 'replacement-planting'
        DB::statement("ALTER TABLE v2_site_reports MODIFY COLUMN planting_status ENUM('no-restoration-expected','not-started','in-progress','completed') NULL");
        DB::statement("ALTER TABLE v2_project_reports MODIFY COLUMN planting_status ENUM('no-restoration-expected','not-started','in-progress','completed') NULL");

        // Optionally remove the option from the option list if present
        $key = 'planting-status';
        $list = FormOptionList::where('key', $key)->first();
        if ($list) {
            $list->options()
                ->where(function ($q) {
                    $q->where('slug', 'replacement-planting')->orWhere('label', 'replacement-planting');
                })
                ->delete();
        }
    }

    private function generateIfMissingI18nItem($target, string $property): ?int
    {
        $value = trim((string) data_get($target, $property, ''));
        $short = strlen($value) <= 256;
        if ($value && empty(data_get($target, $property . '_id'))) {
            $i18nItem = I18nItem::create([
                'type' => $short ? 'short' : 'long',
                'status' => I18nItem::STATUS_DRAFT,
                'short_value' => $short ? $value : null,
                'long_value' => $short ? null : $value,
            ]);

            return $i18nItem->id;
        }

        return data_get($target, $property . '_id');
    }
};


