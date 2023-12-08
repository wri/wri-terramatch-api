<?php

namespace Database\Seeders;

use App\Models\DueSubmission;
use App\Models\Programme;
use App\Models\Site;
use Illuminate\Database\Seeder;

class DueSubmissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $dueSubmission = new DueSubmission();
        $dueSubmission->id = 1;
        $dueSubmission->due_submissionable_type = Programme::class;
        $dueSubmission->due_submissionable_id = 1;
        $dueSubmission->due_at = now()->subDay();
        $dueSubmission->saveOrFail();

        $dueSubmission = new DueSubmission();
        $dueSubmission->id = 2;
        $dueSubmission->due_submissionable_type = Site::class;
        $dueSubmission->due_submissionable_id = 1;
        $dueSubmission->due_at = now();
        $dueSubmission->saveOrFail();

        $dueSubmission = new DueSubmission();
        $dueSubmission->id = 3;
        $dueSubmission->due_submissionable_type = Site::class;
        $dueSubmission->due_submissionable_id = 1;
        $dueSubmission->due_at = now()->subMonth();
        $dueSubmission->saveOrFail();

        $dueSubmission = new DueSubmission();
        $dueSubmission->id = 4;
        $dueSubmission->due_submissionable_type = Site::class;
        $dueSubmission->due_submissionable_id = 1;
        $dueSubmission->due_at = now()->subMonth();
        $dueSubmission->is_submitted = true;
        $dueSubmission->saveOrFail();

        $dueSubmission = new DueSubmission();
        $dueSubmission->id = 5;
        $dueSubmission->due_submissionable_type = Site::class;
        $dueSubmission->due_submissionable_id = 8;
        $dueSubmission->due_at = now()->subMonth();
        $dueSubmission->is_submitted = true;
        $dueSubmission->saveOrFail();
    }
}
