<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateTerrafundCohort3OptionLists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:create-terrafund-cohort3-option-lists';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One-off command: Create option lists for TerraFund Cohort 3 fields (TM-2862)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Creating TerraFund Cohort 3 option lists...');

        $this->createSeedlingsProcurementOptionList();
        $this->addStateLandToLandTenures();
        $this->addOtherToLandownerCollection();

        $this->info('TerraFund Cohort 3 option lists created successfully!');

        return Command::SUCCESS;
    }

    /**
     * Create seedlings-procurement option list
     */
    private function createSeedlingsProcurementOptionList(): void
    {
        $this->info('Creating seedlings-procurement option list...');

        $collections = [
            'seedlings-procurement' => [
                'Direct Purchasing',
                'Growing Own Seedlings',
                'Subcontracting',
            ],
        ];

        foreach ($collections as $key => $items) {
            $formOptionList = FormOptionList::firstOrCreate(['key' => $key]);

            foreach ($items as $item) {
                $slug = Str::slug($item);
                $option = FormOptionListOption::firstOrCreate(
                    [
                        'form_option_list_id' => $formOptionList->id,
                        'slug' => $slug,
                    ],
                    [
                        'label' => $item,
                    ]
                );

                if (empty($option->label_id)) {
                    $option->label_id = $this->generateIfMissingI18nItem($option, 'label');
                    $option->save();
                    $this->info("  ✓ Created option: {$item} ({$slug})");
                } else {
                    $this->line("  - Option already exists: {$item} ({$slug})");
                }
            }
        }
    }

    /**
     * Add state-land option to land-tenures option list
     */
    private function addStateLandToLandTenures(): void
    {
        $this->info('Adding state-land option to land-tenures...');

        $formOptionList = FormOptionList::where('key', 'land-tenures')->first();

        if (! $formOptionList) {
            $this->warn('  ⚠ land-tenures option list not found. Skipping...');

            return;
        }

        $slug = 'state-land';
        $label = 'State Land';

        $option = FormOptionListOption::firstOrCreate(
            [
                'form_option_list_id' => $formOptionList->id,
                'slug' => $slug,
            ],
            [
                'label' => $label,
            ]
        );

        if (empty($option->label_id)) {
            $option->label_id = $this->generateIfMissingI18nItem($option, 'label');
            $option->save();
            $this->info("  ✓ Created option: {$label} ({$slug})");
        } else {
            $this->line("  - Option already exists: {$label} ({$slug})");
        }
    }

    /**
     * Add other option to landowner-collection option list
     */
    private function addOtherToLandownerCollection(): void
    {
        $this->info('Adding other option to landowner-collection...');

        $formOptionList = FormOptionList::where('key', 'landowner-collection')->first();

        if (! $formOptionList) {
            $this->warn('  ⚠ landowner-collection option list not found. Skipping...');

            return;
        }

        $slug = 'other';
        $label = 'Other';

        $option = FormOptionListOption::firstOrCreate(
            [
                'form_option_list_id' => $formOptionList->id,
                'slug' => $slug,
            ],
            [
                'label' => $label,
            ]
        );

        if (empty($option->label_id)) {
            $option->label_id = $this->generateIfMissingI18nItem($option, 'label');
            $option->save();
            $this->info("  ✓ Created option: {$label} ({$slug})");
        } else {
            $this->line("  - Option already exists: {$label} ({$slug})");
        }
    }

    /**
     * Generate I18n item if missing
     */
    private function generateIfMissingI18nItem(FormOptionListOption $option, string $property): ?int
    {
        $value = trim(data_get($option, $property, false));
        $short = strlen($value) <= 256;

        if ($value && empty($option->label_id)) {
            $i18nItem = I18nItem::create([
                'type' => $short ? 'short' : 'long',
                'status' => I18nItem::STATUS_DRAFT,
                'short_value' => $short ? $value : null,
                'long_value' => $short ? null : $value,
            ]);

            return $i18nItem->id;
        }

        return $option->label_id;
    }
}
