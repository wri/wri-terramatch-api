<?php

namespace Tests\V2\Forms;

use App\Models\V2\Forms\Form;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IndexFormControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_view_form_index(): void
    {
        $count = Form::count();
        $user = User::factory()->admin()->create();

        Form::factory()->count(5)->create();
        $matchedForm = Form::factory()->create([
            'title' => 'this is a long unique title',
        ]);
        $newestForm = Form::factory()->create([
            'created_at' => now()->addDecade(),
        ]);

        // assert a regular index
        $this->actingAs($user)
            ->getJson('/api/v2/admin/forms')
            ->assertStatus(200)
            ->assertJsonCount(7 + $count, 'data');

        // assert searching by title
        $this->actingAs($user)
            ->getJson('/api/v2/admin/forms?filter[title]=long%20unique')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // assert filtering by stage ID
        $this->actingAs($user)
            ->getJson('/api/v2/admin/forms?filter[stage_id]=' . $matchedForm->stage_id)
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'uuid' => $matchedForm->uuid,
            ]);

        // assert sorting by created at
        $this->actingAs($user)
            ->getJson('/api/v2/admin/forms?sort=-created_at')
            ->assertStatus(200)
            ->assertJsonCount(7 + $count, 'data')
            ->assertJsonPath('data.0.uuid', $newestForm->uuid);
    }

    public function test_users_can_view_form_index(): void
    {
        $count = Form::count();
        $user = User::factory()->create();

        Form::factory()->count(5)->create();
        $matchedForm = Form::factory()->create([
            'title' => 'this is a long unique title',
        ]);
        $newestForm = Form::factory()->create([
            'created_at' => now()->addDecade(),
        ]);

        // assert a regular index
        $this->actingAs($user)
            ->getJson('/api/v2/forms')
            ->assertStatus(200)
            ->assertJsonCount(7 + $count, 'data');

        // assert searching by title
        $this->actingAs($user)
            ->getJson('/api/v2/forms?filter[title]=long%20unique')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // assert filtering by stage ID
        $this->actingAs($user)
            ->getJson('/api/v2/forms?filter[stage_id]=' . $matchedForm->stage_id)
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'uuid' => $matchedForm->uuid,
            ]);

        // assert sorting by created at
        $this->actingAs($user)
            ->getJson('/api/v2/forms?sort=-created_at')
            ->assertStatus(200)
            ->assertJsonCount(7 + $count, 'data')
            ->assertJsonPath('data.0.uuid', $newestForm->uuid);
    }
}
