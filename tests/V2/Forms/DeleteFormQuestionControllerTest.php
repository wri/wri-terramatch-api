<?php

namespace Tests\V2\Forms;

use App\Models\V2\Forms\FormQuestion;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteFormQuestionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_delete_form_questions()
    {
        $user = User::factory()->admin()->create();
        $formQuestion = FormQuestion::factory()->create();

        $this->assertNull($formQuestion->deleted_at);

        $response = $this->actingAs($user)
            ->deleteJson('/api/v2/admin/forms/question/' . $formQuestion->uuid)
            ->assertStatus(200);

        $formQuestion = FormQuestion::isUuid($response->json('data.uuid'))->withTrashed()->first();
        $this->assertNotNull($formQuestion->deleted_at);
    }

    public function test_non_admins_cannot_delete_form_questions()
    {
        $user = User::factory()->create();
        $formQuestion = FormQuestion::factory()->create();

        $this->actingAs($user)
            ->deleteJson('/api/v2/admin/forms/question/' . $formQuestion->uuid)
            ->assertStatus(403);
    }
}
