<?php

namespace App\Console\Commands\OneOff;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveBudgetFieldsFromOrganisations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:remove-budget-fields-from-organisations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes budget fields from organisations table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Removing budget fields from organisations table...');

        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn([
                'fin_budget_current_year',
                'fin_budget_1year',
                'fin_budget_2year',
                'fin_budget_3year',
                'organisation_revenue_this_year',
            ]);
        });

        $this->info('Budget fields removed successfully from organisations table.');
    }
}
