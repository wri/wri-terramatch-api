<?php

use Illuminate\Database\Seeder;
use App\Models\PasswordReset as PasswordResetModel;

class PasswordResetsTableSeeder extends Seeder
{
    public function run()
    {
        $passwordReset = new PasswordResetModel();
        $passwordReset->user_id = 1;
        $passwordReset->token = "kmaBxJbn2NyfbLAIAAQtQGGdiJmyIblS";
        $passwordReset->saveOrFail();
    }
}
