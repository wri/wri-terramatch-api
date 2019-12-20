<?php

use Illuminate\Database\Seeder;
use App\Models\Verification as VerificationModel;

class VerificationsTableSeeder extends Seeder
{
    public function run()
    {
        $verification = new VerificationModel();
        $verification->user_id = 1;
        $verification->token = "ejyNeH1Rc26qNJeok932fGUv8GyNqMs4";
        $verification->saveOrFail();
    }
}
