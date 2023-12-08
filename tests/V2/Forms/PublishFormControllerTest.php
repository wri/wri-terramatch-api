<?php

namespace Tests\V2\Forms;

use App\Models\User;
use App\Models\V2\Forms\Form;
use App\Models\V2\Stages\Stage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublishFormControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_publish_forms(): void
    {
        $user = User::factory()->admin()->create();
        $stage = Stage::factory()->create();
        $oldForm = Form::factory()->published()->create([
            'version' => 1,
            'stage_id' => $stage,
        ]);
        $form = Form::factory()->unpublished()->create([
            'version' => 2,
            'stage_id' => $stage,
        ]);

        $this->assertTrue($oldForm->published);
        $this->assertFalse($form->published);
        $this->actingAs($user)
            ->patchJson('/api/v2/admin/forms/' . $form->uuid . '/publish')
            ->assertStatus(200)
            ->assertJsonFragment([
                'version' => 2,
                'published' => true,
            ]);

        $oldForm->refresh();
        $form->refresh();
        $this->assertTrue($form->published);
        $this->assertFalse($oldForm->published);
    }

    public function test_non_admins_cannot_publish_forms(): void
    {
        $user = User::factory()->create();
        $form = Form::factory()->unpublished()->create();

        $this->assertFalse($form->published);
        $this->actingAs($user)
            ->patchJson('/api/v2/admin/forms/' . $form->uuid . '/publish')
            ->assertStatus(403);

        $form->refresh();
        $this->assertFalse($form->published);
    }
}
