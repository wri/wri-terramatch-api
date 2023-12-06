<?php

namespace Tests\V2\Forms;

use App\Models\User;
use App\Models\V2\Forms\FormOptionList;
use App\Models\V2\Forms\FormOptionListOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommonOptionsIndexControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_action()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $buckets = ['alpha', 'bravo'];
        $things = ['Australia','New Zealand','Colombia','Pakistan','Netherlands' ];

        foreach ($buckets as $bucket) {
            $list = FormOptionList::factory()->create(['key' => $bucket]);
            foreach ($things as $thing) {
                FormOptionListOption::factory()->create(['form_option_list_id' => $list->id, 'label' => $thing]);
            }
        }
        $uri = '/api/v2/admin/forms/common-options/';

        $this->actingAs($user)
            ->getJson($uri . 'alpha')
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson($uri . 'bravo')
            ->assertSuccessful()
            ->assertJsonCount(5, 'data');

        $this->actingAs($admin)
            ->getJson($uri . 'bravo?search=land')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data');
    }
}
