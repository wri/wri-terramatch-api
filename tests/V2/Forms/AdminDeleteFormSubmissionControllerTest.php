<?php

namespace Tests\V2\Forms;

use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDeleteFormSubmissionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_delete_form_submissions()
    {
        $user = User::factory()->admin()->create();
        $formSubmission = FormSubmission::factory()->create();

        $this->actingAs($user)
            ->deleteJson('/api/v2/admin/forms/submissions/' . $formSubmission->uuid)
            ->assertStatus(200);
    }

    public function test_users_cannot_delete_form_submissions()
    {
        $user = User::factory()->create();
        $formSubmission = FormSubmission::factory()->create();

        $this->actingAs($user)
            ->deleteJson('/api/v2/admin/forms/submissions/' . $formSubmission->uuid)
            ->assertStatus(403);
    }
}
