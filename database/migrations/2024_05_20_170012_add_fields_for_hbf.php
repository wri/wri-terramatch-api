<?php

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public const NEW_INTERVENTION_LABEL = 'Removal/management of invasive species';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->tinyInteger('pct_employees_marginalised')->nullable();
            $table->tinyInteger('pct_beneficiaries_marginalised')->nullable();
            $table->tinyInteger('pct_beneficiaries_men')->nullable();
        });
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->text('detailed_intervention_types')->nullable();
            $table->text('proj_impact_foodsec')->nullable();
            $table->tinyInteger('pct_employees_marginalised')->nullable();
            $table->tinyInteger('pct_beneficiaries_marginalised')->nullable();
            $table->tinyInteger('pct_beneficiaries_men')->nullable();
            $table->text('proposed_gov_partners')->nullable();
            $table->unsignedInteger('proposed_num_nurseries')->nullable();
            $table->text('proj_boundary')->nullable();
        });
        Schema::table('v2_sites', function (Blueprint $table) {
            $table->text('detailed_intervention_types')->nullable();
        });

        $formOptionList = FormOptionList::where('key', 'interventions')->first();
        $formOptionList->options()->create([
            'label' => self::NEW_INTERVENTION_LABEL,
            'label_id' => $this->generateI18nItem(self::NEW_INTERVENTION_LABEL),
            'slug' => Str::slug(self::NEW_INTERVENTION_LABEL),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_pitches', function (Blueprint $table) {
            $table->dropColumn('pct_employees_marginalised');
            $table->dropColumn('pct_beneficiaries_marginalised');
            $table->dropColumn('pct_beneficiaries_men');
        });
        Schema::table('v2_projects', function (Blueprint $table) {
            $table->dropColumn('detailed_intervention_types');
            $table->dropColumn('proj_impact_foodsec');
            $table->dropColumn('pct_employees_marginalised');
            $table->dropColumn('pct_beneficiaries_marginalised');
            $table->dropColumn('pct_beneficiaries_men');
            $table->dropColumn('proposed_gov_partners');
            $table->dropColumn('proposed_num_nurseries');
            $table->dropColumn('proj_boundary');
        });
        Schema::table('v2_sites', function (Blueprint $table) {
            $table->dropColumn('detailed_intervention_types');
        });
        $formOptionList = FormOptionList::where('key', 'interventions')->first();
        $formOptionList->options()->where('slug', Str::slug(self::NEW_INTERVENTION_LABEL))->forceDelete();
    }

    private function generateI18nItem(string $source): ?int
    {
        $value = trim($source);
        $short = strlen($value) <= 256;

        return I18nItem::create([
            'type' => $short ? 'short' : 'long',
            'status' => I18nItem::STATUS_DRAFT,
            'short_value' => $short ? $value : null,
            'long_value' => $short ? null : $value,
        ])->id;
    }
};
