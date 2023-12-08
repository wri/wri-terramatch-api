<?php

namespace Database\Seeders;

use App\Models\PasswordReset as PasswordResetModel;
use Illuminate\Database\Seeder;

class PasswordResetsTableSeeder extends Seeder
{
    public function run()
    {
        $passwordReset = new PasswordResetModel();
        $passwordReset->id = 1;
        $passwordReset->user_id = 1;
        $passwordReset->token = 'kmaBxJbn2NyfbLAIAAQtQGGdiJmyIblS';
        $passwordReset->saveOrFail();
    }
}
