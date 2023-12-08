<?php

namespace Database\Seeders;

use App\Helpers\ProgressUpdateHelper;
use App\Jobs\CreateThumbnailsJob;
use App\Models\ProgressUpdate as ProgressUpdateModel;
use Illuminate\Database\Seeder;

class ProgressUpdatesTableSeeder extends Seeder
{
    public function run()
    {
        $progressUpdate = new ProgressUpdateModel();
        $progressUpdate->id = 1;
        $progressUpdate->monitoring_id = 2;
        $progressUpdate->grouping = 'general';
        $progressUpdate->title = 'Foo foo foo';
        $progressUpdate->breakdown = 'Bar bar bar';
        $progressUpdate->summary = 'Baz baz baz';
        $progressUpdate->data = ProgressUpdateHelper::total([
            'planting_date' => '2021-01-01',
            'trees_planted' => [
                [
                    'name' => 'maple',
                    'value' => 1,
                ],
                [
                    'name' => 'oak',
                    'value' => 2,
                ],
                [
                    'name' => 'sycamore',
                    'value' => 3,
                ],
            ],
            'survival_rate' => 100,
            'short_term_jobs_amount' => [
                'male' => 1,
                'female' => 2,
            ],
            'biodiversity_update' => 'Norf norf norf',
        ]);
        $image = DatabaseSeeder::seedRandomObject('image');
        $images = [
            [
                'image' => $image,
                'caption' => 'Qux qux qux',
            ],
        ];
        DatabaseSeeder::setRawAttribute($progressUpdate, 'images', json_encode($images));
        $progressUpdate->created_by = 3;
        $progressUpdate->saveOrFail();
        CreateThumbnailsJob::dispatchSync($progressUpdate);

        $progressUpdate = new ProgressUpdateModel();
        $progressUpdate->id = 2;
        $progressUpdate->monitoring_id = 2;
        $progressUpdate->grouping = 'planting';
        $progressUpdate->title = 'Foo foo foo';
        $progressUpdate->breakdown = 'Bar bar bar';
        $progressUpdate->summary = 'Baz baz baz';
        $progressUpdate->data = ProgressUpdateHelper::total([
            'planting_date' => '2021-01-02',
            'trees_planted' => [
                [
                    'name' => 'pine',
                    'value' => 1,
                ],
                [
                    'name' => 'oak',
                    'value' => 2,
                ],
            ],
            'survival_rate' => 50,
            'short_term_jobs_amount' => [
                'male' => 2,
                'female' => 0,
            ],
            'biodiversity_update' => 'Norf norf norf',
        ]);
        $image = DatabaseSeeder::seedRandomObject('image');
        $images = [
            [
                'image' => $image,
                'caption' => 'Norf norf norf',
            ],
        ];
        DatabaseSeeder::setRawAttribute($progressUpdate, 'images', json_encode($images));
        $progressUpdate->created_by = 3;
        $progressUpdate->created_at = '2022-12-31 23:59:59';
        $progressUpdate->updated_at = '2022-12-31 23:59:59';
        $progressUpdate->saveOrFail();
        CreateThumbnailsJob::dispatchSync($progressUpdate);
    }
}
