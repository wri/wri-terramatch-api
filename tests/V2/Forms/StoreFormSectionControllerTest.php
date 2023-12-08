<?php

namespace Tests\V2\Forms;

use App\Models\User;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StoreFormSectionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminsCanCreateFormSections(): void
    {
        $user = User::factory()->admin()->create();
        $form = Form::factory()->create();

        $payload = [
            'form_id' => $form->uuid,
            'order' => 1,
        ];

        $this->assertFalse($form->updated_by === $user->id);
        $response = $this->actingAs($user)
            ->postJson('/api/v2/admin/forms/section', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'form_id' => $form->uuid,
                'order' => 1,
        ]);
        $formSection = FormSection::where('uuid', $response->json('data.uuid'))->first();
        $this->assertTrue($formSection->form->updated_by === $user->uuid);
    }

    public function testFormSectionOrderIsUniqueByForm(): void
    {
        $user = User::factory()->admin()->create();
        $form = Form::factory()->create();
        FormSection::factory()->create([
            'order' => 2,
        ]);

        $this->actingAs($user)
            ->postJson('/api/v2/admin/forms/section', [
                'form_id' => $form->uuid,
                'order' => 1,
            ])
            ->assertStatus(201);

        $this->actingAs($user)
            ->postJson('/api/v2/admin/forms/section', [
                'form_id' => $form->uuid,
                'order' => 2,
            ])
            ->assertStatus(201);

        $this->actingAs($user)
            ->postJson('/api/v2/admin/forms/section', [
                'form_id' => $form->uuid,
                'order' => 2,
            ])
            ->assertStatus(422);
    }

    public function testNonAdminsCannotCreateFormSections(): void
    {
        $user = User::factory()->create();
        $form = Form::factory()->create();

        $payload = [
            'form_id' => $form->uuid,
            'order' => 1,
        ];

        $this->actingAs($user)
            ->postJson('/api/v2/admin/forms/section', $payload)
            ->assertStatus(403);
    }
}
