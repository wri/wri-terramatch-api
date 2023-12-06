<?php

namespace Database\Seeders;

use App\Models\Verification as VerificationModel;
use Illuminate\Database\Seeder;

class VerificationsTableSeeder extends Seeder
{
    public function run()
    {
        $verification = new VerificationModel();
        $verification->id = 1;
        $verification->user_id = 1;
        $verification->token = 'ejyNeH1Rc26qNJeok932fGUv8GyNqMs4';
        $verification->saveOrFail();

        $verification = new VerificationModel();
        $verification->id = 2;
        $verification->user_id = 5;
        $verification->token = 'fjyNeH1Rc26qNJeok932fGUv8GyNqMs4';
        $verification->saveOrFail();
    }
}
