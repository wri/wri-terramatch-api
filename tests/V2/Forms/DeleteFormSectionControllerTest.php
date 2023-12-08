<?php

namespace Tests\V2\Forms;

use App\Models\User;
use App\Models\V2\Forms\FormSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteFormSectionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_delete_form_sections()
    {
        $user = User::factory()->admin()->create();
        $formSection = FormSection::factory()->create([
            'order' => 3,
        ]);

        $this->assertNull($formSection->deleted_at);

        $response = $this->actingAs($user)
            ->deleteJson('/api/v2/admin/forms/section/' . $formSection->uuid)
            ->assertStatus(200);

        $formSection = FormSection::isUuid($response->json('data.uuid'))->withTrashed()->first();
        $this->assertNotNull($formSection->deleted_at);
    }

    public function test_non_admins_cannot_delete_form_sections()
    {
        $user = User::factory()->create();
        $formSection = FormSection::factory()->create([
            'order' => 3,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/v2/admin/forms/section/' . $formSection->uuid)
            ->assertStatus(403);
    }
}
