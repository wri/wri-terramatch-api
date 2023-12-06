<?php

namespace Tests\V2\Forms;

use App\Models\User;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateFormSectionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_update_form_sections(): void
    {
        $user = User::factory()->admin()->create();
        $formSection = FormSection::factory()->create([
            'order' => 3,
        ]);

        $payload = [
            'order' => 1,
        ];

        $this->assertFalse($formSection->form->updated_by === $user->uuid);

        $response = $this->actingAs($user)
            ->patchJson('/api/v2/admin/forms/section/' . $formSection->uuid, $payload)
            ->assertStatus(200)
            ->assertJsonFragment([
                'form_id' => $formSection->form_id,
                'order' => 1,
        ]);

        $formSection = FormSection::isUuid($response->json('data.uuid'))->first();
        $this->assertTrue($formSection->form->updated_by === $user->uuid);
    }

    public function test_form_section_order_is_unique_by_form(): void
    {
        $user = User::factory()->admin()->create();
        $form = Form::factory()->create();
        $formSection = FormSection::factory()->create([
            'form_id' => $form->uuid,
            'order' => 2,
        ]);
        FormSection::factory()->create([
            'form_id' => $form->uuid,
            'order' => 1,
        ]);

        $this->actingAs($user)
            ->patchJson('/api/v2/admin/forms/section/' . $formSection->uuid, [
                'order' => 3,
            ])
            ->assertStatus(200);

        $this->actingAs($user)
            ->patchJson('/api/v2/admin/forms/section/' . $formSection->uuid, [
                'form_id' => $form->uuid,
                    'order' => 3,
                ])
                ->assertStatus(422);
    }

    public function testNonAdminsCannotUpdateFormSections(): void
    {
        $user = User::factory()->create();
        $formSection = FormSection::factory()->create([
            'order' => 3,
        ]);

        $payload = [
            'order' => 1,
        ];

        $this->actingAs($user)
            ->patchJson('/api/v2/admin/forms/section/' . $formSection->uuid, $payload)
            ->assertStatus(403);
    }
}
