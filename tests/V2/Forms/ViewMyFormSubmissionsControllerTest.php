<?php

namespace Tests\V2\Forms;

use App\Models\User;
use App\Models\V2\Forms\Form;
use App\Models\V2\Forms\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ViewMyFormSubmissionsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_their_form_submissions(): void
    {
        $user = User::factory()->admin()->create();

        $form = Form::factory()->create();
        FormSubmission::factory()->count(5)->create();
        FormSubmission::factory()->count(10)->create([
            'user_id' => $user->uuid,
            'form_id' => $form->uuid,
        ]);

        $this->actingAs($user)
            ->getJson('/api/v2/forms/my/submissions')
            ->assertStatus(200)
            ->assertJsonCount(10, 'data');
    }
}
