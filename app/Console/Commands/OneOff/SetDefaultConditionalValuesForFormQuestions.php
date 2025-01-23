<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Forms\FormQuestion;
use Illuminate\Console\Command;

class SetDefaultConditionalValuesForFormQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:set-default-conditional-values-for-form-questions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets a default value for conditional values in form questions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        FormQuestion::whereIn('id', [
            875,878,880,900,907,981,985,1001,1088,1089,1090,1097,1103,1104,1105,1118,1180,1216,1219,1274,1275,1291,1295,1311,1520,1540,1605,1625,1705,1707,1709,1727,1795,1815,1845,1846,1860,1878,1891,1900,1931,1935,1951,1963,1965,2990,2994,3010,3022,3024,3051,3055,3071,3083,3085,3105,3106,3384,3386,3388,3398,3400,3406,3408,3410,3420,3422,3443,3470,3472,3474,3484,3486,
        ])->update([
            'conditional_default' => false,
        ]);
    }
}
