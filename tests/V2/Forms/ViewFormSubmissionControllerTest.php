<?php

namespace Tests\V2\Forms;

use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ViewFormSubmissionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_view_form_submissions(): void
    {
        $user = User::factory()->admin()->create();
        $formSubmission = FormSubmission::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v2/admin/forms/submissions/' . $formSubmission->uuid)
            ->assertStatus(200);
    }

    public function test_uers_can_view_their_form_submissions(): void
    {
        $user = User::factory()->admin()->create();
        $formSubmission = FormSubmission::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/v2/forms/submissions/' . $formSubmission->uuid)
            ->assertStatus(200);
    }

    public function test_users_cannot_view_other_form_submissions(): void
    {
        $user = User::factory()->create();
        $formSubmission = FormSubmission::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v2/forms/submissions/' . $formSubmission->uuid)
            ->assertStatus(403);
    }
}
