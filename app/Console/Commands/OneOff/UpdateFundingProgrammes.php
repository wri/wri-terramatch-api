<?php

namespace App\Console\Commands\OneOff;

use App\Models\Framework;
use App\Models\V2\FundingProgramme;
use Illuminate\Console\Command;

class UpdateFundingProgrammes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:update-funding-programmes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Funding Programme updates April 2025. TM-1866';

    protected const HBF_ENTERPRISES = '86b3ea32-8541-4525-b342-2d8010b3cdf7';
    protected const HBF_NON_PROFIT = 'a8a453a8-658c-48f3-ab79-cf23217bc8ed';
    protected const TF_LANDSCAPES_ENTERPRISES = 'e80f1187-6ece-4803-a145-7b48c514cc00';
    protected const TF_LANDSCAPES_NON_PROFIT = '18f1af1f-8ff3-494b-98e6-1d1c0d44d5d9';
    protected const UPDATES = [
        [
            'uuids' => [self::HBF_ENTERPRISES],
            'update' => ['framework_key' => 'hbf'],
        ],
        [
            'uuids' => [self::TF_LANDSCAPES_ENTERPRISES],
            'update' => ['framework_key' => 'enterprises'],
        ],
        [
            'uuids' => [self::HBF_NON_PROFIT, self::TF_LANDSCAPES_NON_PROFIT],
            'update' => ['organisation_types' => ['non-profit-organisation']],
        ],
    ];

    protected const TO_REMOVE = [
        # 3SC: Production Deployment Testing
        '7143054e-b03d-4087-a596-d4507207ca36',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating fields on existing funding programmes...');
        foreach (self::UPDATES as $update) {
            FundingProgramme::whereIn('uuid', $update['uuids'])->update($update['update']);
        }

        $this->info('Creating dummy programmes for old frameworks...');
        // Dummy programs
        FundingProgramme::create([
            'name' => 'Priceless Planet Coalition',
            'framework_key' => 'ppc',
            'organisation_types' => ['for-profit-organization','non-profit-organization'],
            'description' => 'Dummy application for PPC projects',
        ]);
        FundingProgramme::create([
            'name' => 'TerraFund for AFR100 (Enterprises)',
            'framework_key' => 'enterprises',
            'organisation_types' => ['for-profit-organization'],
            'location' => 'Africa- all AFR100 Initiative countries',
            'description' => 'Dummy application for TerraFund Top 100 enterprises',
        ]);
        FundingProgramme::create([
            'name' => 'TerraFund for AFR100 (Non-Profits)',
            'framework_key' => 'terrafund',
            'organisation_types' => ['non-profit-organization'],
            'location' => 'Africa- all AFR100 Initiative countries',
            'description' => 'Dummy application for TerraFund Top 100 non-profits',
        ]);

        $this->info('Creating new programmes for the next application cycle...');
        // New programs - Note that we are not setting up the two new Frameworks at this time. That will come down the
        // road in time for applications to be approved.
        FundingProgramme::create([
            'name' => 'TerraFund for AFR100: Biodiversity and Food Security (Enterprises)',
            'framework_key' => 'enterprises',
            'organisation_types' => ['for-profit-organization'],
            'location' => 'Kenya’s Greater Rift Valley; the Lake Kivu & Rusizi River Basin of Burundi, DRC, and Rwanda; and the Ghana Cocoa Belt.',
            'description' => 'Funding projects based in three African Landscapes: the Ghana Cocoa Belt, the Greater Rusizi Basin of Burundi, the Democratic Republic of the Congo, and Rwanda, and the Great Rift Valley of Kenya, focusing on promoting biodiversity and food security. This application is for enterprises only.',
        ]);
        FundingProgramme::create([
            'name' => 'TerraFund for AFR100: Biodiversity and Food Security (Non-Profits)',
            'framework_key' => 'landscapes',
            'organisation_types' => ['non-profit-organization'],
            'location' => 'Kenya’s Greater Rift Valley; the Lake Kivu & Rusizi River Basin of Burundi, DRC, and Rwanda; and the Ghana Cocoa Belt.',
            'description' => 'Funding projects based in three African Landscapes: the Ghana Cocoa Belt, the Greater Rusizi Basin of Burundi, the Democratic Republic of the Congo, and Rwanda, and the Great Rift Valley of Kenya, focusing on promoting biodiversity and food security. This application is for non-profits only.',
        ]);
        FundingProgramme::create([
            'name' => 'Fundo Flora Cohort 1 (Enterprises)',
            'framework_key' => 'enterprises',
            'organisation_types' => ['for-profit-organization'],
            'location' => 'State of Pará, Brazil',
            'description' => 'Funding projects in the Brazilian State of Pará. This application is for enterprises only.',
        ]);
        FundingProgramme::create([
            'name' => 'Fundo Flora Cohort 1 (Non-Profits)',
            'framework_key' => 'fundo-flora',
            'organisation_types' => ['non-profit-organization'],
            'location' => 'State of Pará, Brazil',
            'description' => 'Funding projects in the Brazilian state of Pará. This application is for non-profits only.',
        ]);

        $this->info('Removing old test programmes...');
        FundingProgramme::whereIn('uuid', self::TO_REMOVE)->delete();

        $this->info('Updating enterprises framework access code...');
        Framework::where('slug', 'enterprises')->update(['access_code' => 'enterprises']);
    }
}
