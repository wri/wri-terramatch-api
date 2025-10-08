<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert existing snake_case values to kebab-case
        $conversions = [
            'private_grant' => 'private-grant',
            'public_grant' => 'public-grant',
            'loan_credit_private' => 'loan-credit-private',
            'private_equity' => 'private-equity',
            'product_offtake_contract' => 'product-offtake-contract',
            'carbon_credits_contract' => 'carbon-credits-contract',
            'ecosystem_services' => 'ecosystem-services',
            'other' => 'other', // 'other' doesn't need conversion
        ];

        foreach ($conversions as $oldValue => $newValue) {
            DB::table('v2_funding_types')
                ->where('type', $oldValue)
                ->update(['type' => $newValue]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert kebab-case values back to snake_case
        $conversions = [
            'private-grant' => 'private_grant',
            'public-grant' => 'public_grant',
            'loan-credit-private' => 'loan_credit_private',
            'private-equity' => 'private_equity',
            'product-offtake-contract' => 'product_offtake_contract',
            'carbon-credits-contract' => 'carbon_credits_contract',
            'ecosystem-services' => 'ecosystem_services',
            'other' => 'other', // 'other' doesn't need conversion
        ];

        foreach ($conversions as $oldValue => $newValue) {
            DB::table('v2_funding_types')
                ->where('type', $oldValue)
                ->update(['type' => $newValue]);
        }
    }
};
