<?php

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\Forms\FormQuestionOption;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            if (! Schema::hasColumn('v2_sites', 'land_tenure_approach')) {
                Schema::table('v2_sites', function (Blueprint $table): void {
                    $table->text('land_tenure_approach')->nullable();
                });
            }

            if (Schema::hasColumn('v2_projects', 'land_tenure_approach')) {
                Schema::table('v2_projects', function (Blueprint $table): void {
                    $table->dropColumn('land_tenure_approach');
                });
            }

            $options = [
                'indigenous-land' => 'Indigenous Land',
                'extractive-reserve-resex' => 'Extractive Reserve (RESEX)',
                'sustainable-development-reserve-rds' => 'Sustainable Development Reserve (RDS)',
                'national-forest-flona' => 'National Forest (FLONA)',
                'environmental-protection-area-apa' => 'Environmental Protection Area (APA)',
                'rural-settlements-pae-paex-or-pds' => 'Rural Settlements (PAE, PAEX, or PDS)',
                'quilombola-land' => 'Quilombola Land',
                'public-land' => 'Public Land',
                'private-land' => 'Private Land',
                'other-land' => 'Other Land',
            ];

            $optionList = FormOptionList::where('key', 'land-tenures')->first();
            if ($optionList) {
                foreach ($options as $slug => $label) {
                    $option = FormOptionListOption::firstOrCreate(
                        [
                            'form_option_list_id' => $optionList->id,
                            'slug' => $slug,
                        ],
                        [
                            'label' => $label,
                        ]
                    );

                    if (empty($option->label_id)) {
                        $option->label_id = $this->generateIfMissingI18nItem($option, 'label');
                        $option->save();
                    }
                }
            }

            $questionIds = FormQuestion::where('linked_field_key', 'pro-land-tenure-proj-area')->pluck('id');
            foreach ($questionIds as $questionId) {
                $nextOrder = (int) FormQuestionOption::where('form_question_id', $questionId)->max('order');

                foreach ($options as $slug => $label) {
                    $existing = FormQuestionOption::where('form_question_id', $questionId)
                        ->where('slug', $slug)
                        ->first();

                    if ($existing) {
                        if (empty($existing->label_id)) {
                            $existing->label_id = $this->generateIfMissingI18nItem($existing, 'label');
                            $existing->save();
                        }

                        continue;
                    }

                    $nextOrder++;
                    $questionOption = FormQuestionOption::create([
                        'form_question_id' => $questionId,
                        'order' => $nextOrder,
                        'slug' => $slug,
                        'label' => $label,
                    ]);

                    if (empty($questionOption->label_id)) {
                        $questionOption->label_id = $this->generateIfMissingI18nItem($questionOption, 'label');
                        $questionOption->save();
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::transaction(function (): void {
            if (! Schema::hasColumn('v2_projects', 'land_tenure_approach')) {
                Schema::table('v2_projects', function (Blueprint $table): void {
                    $table->text('land_tenure_approach')->nullable();
                });
            }

            if (Schema::hasColumn('v2_sites', 'land_tenure_approach')) {
                Schema::table('v2_sites', function (Blueprint $table): void {
                    $table->dropColumn('land_tenure_approach');
                });
            }

            $slugs = [
                'indigenous-land',
                'extractive-reserve-resex',
                'sustainable-development-reserve-rds',
                'national-forest-flona',
                'environmental-protection-area-apa',
                'rural-settlements-pae-paex-or-pds',
                'quilombola-land',
                'public-land',
                'private-land',
                'other-land',
            ];

            $optionList = FormOptionList::where('key', 'land-tenures')->first();
            if ($optionList) {
                FormOptionListOption::where('form_option_list_id', $optionList->id)
                    ->whereIn('slug', $slugs)
                    ->delete();
            }

            $questionIds = FormQuestion::where('linked_field_key', 'pro-land-tenure-proj-area')->pluck('id');
            FormQuestionOption::whereIn('form_question_id', $questionIds)
                ->whereIn('slug', $slugs)
                ->delete();
        });
    }

    private function generateIfMissingI18nItem(Model $target, string $property): ?int
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
