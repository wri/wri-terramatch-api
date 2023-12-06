<?php

namespace Database\Seeders;

use App\Models\ElevatorVideo as ElevatorVideoModel;
use Illuminate\Database\Seeder;

class ElevatorVideosTableSeeder extends Seeder
{
    public function run()
    {
        $elevatorVideo = new ElevatorVideoModel();
        $elevatorVideo->id = 1;
        $elevatorVideo->user_id = 3;
        $elevatorVideo->status = 'finished';
        $elevatorVideo->job_id = '3333333333333-abcde3';
        $elevatorVideo->upload_id = 1;
        $elevatorVideo->saveOrFail();
    }
}
