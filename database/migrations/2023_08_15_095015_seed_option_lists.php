<?php

use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

class SeedOptionLists extends Migration
{
    /**
     * This migration seeds the default options
     *
     * @return void
     */
    public function up()
    {
        $collections = [
            'organisation-type' => ['For-Profit Organization', 'Non-Profit Organization', 'Government Agency'],
            'business-type' => ['Private Limited', 'LLP', 'Registered Partnership', 'Sole Proprietorship', 'Non-Profit'],
            'languages' => ['English', 'French', 'Spanish', 'portugese', 'Hindi', 'Marathi'],
            'loan-status' => ['Working Capital', 'Team and New Hires', 'Marketing', 'Capital Investment'],
            'engagement-farmers' => ['We provide paid jobs for farmers', 'We directly engage & benefit farmers', 'We provide indirect benefits to farmers', 'We do not engage with farmers'],
            'engagement-women' => ['We provide paid jobs for women', 'We directly engage and benefit women', 'We provide indirect benefits to women', 'We do not engage with women'],
            'engagement-youth' => ['We provide paid jobs for people younger than 29', 'We directly engage and benefit younger than 29', 'We provide indirect benefits to people younger than 29',
                'We do not engage with people younger than 29'],
            'engagement-non-youth' => ['We provide paid jobs for people older than 29', 'We directly engage and benefit older than 29', 'We provide indirect benefits to people older than 29',
                'We do not engage with people older than 29'],
            'restoration-systems' => ['Agroforest' ,'Grassland' ,'Natural Forest' ,'Mangrove' ,'Peatland' ,'Riparian Area or Wetland' ,'Silvopasture' ,'Urban Forest' ,'Woodlot or Plantation'],
            'restoration-practices' => ['Tree Planting' ,'Assisted Natural Regeneration' ,'Direct Seeding'],
            'interventions' => ['Farm Forestry' ,'Intercropping' ,'Agri-Horti-Forestry (Wadi)' ,'Mixed Species Plantation' ,'Farmer Managed Natural Regeneration' ,'Pastureland Development' ,
                'Grassland Restoration' ,'Trees on Bunds and Boundaries' ,'Bamboo Plantation' ,'Protection and Sustainable Harvesting of Forest Produce' ,
                'Value Chain Development for Non-Timber Forest Produce and Indigenous Crops' ,'Sustainable Agriculture/Food Forest Model' ,'Watershed Management' ,'Soil and Moisture Conservation' ,
                'Soil Carbon and Microbiome Management'],
            'building-needs' => ['Site Selection', 'Nursery Management', 'Species Selection', 'Community Engagement', 'Narrative Reporting', 'Field Monitoring', 'Remote Sensing', 'Accounting & Budgeting',
                'Proposal Writing', 'Government Engagement', 'Certifications', 'Communications', 'Gender & Social Equity', 'Supply Chain Development', 'Product Marketing', 'Environmental Impact',
                'Socio-economic impact'],
            'media-channels' => ['Land Accelerator South Asia', 'WhatsApp', 'LinkedIn', 'Facebook', 'Twitter', 'WRI India', 'Sangam', 'Internet search', 'Email'],
        ];

        foreach ($collections as $key => $items) {
            $formOptionList = FormOptionList::create(['key' => $key]);

            foreach ($items as $item) {
                $options = FormOptionListOption::create([
                    'form_option_list_id' => $formOptionList->id,
                    'label' => $item,
                    'slug' => Str::slug($item),
                ]);
            }
        }

        foreach (FormOptionListOption::all() as $option) {
            $option->label_id = $this->generateIfMissingI18nItem($option, 'label');
            $option->save();
        }
    }

    private function generateIfMissingI18nItem(Model $target, string $property): ?int
    {
        $value = trim(data_get($target, $property, false));
        $short = strlen($value) <= 256;
        if ($value && data_get($target, $property . '_id', true)) {
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
}
