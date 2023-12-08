<?php

namespace Tests\V2\Forms;

use App\Models\User;
use App\Models\V2\Forms\Form;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class ViewFormControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_view_existing_form(): void
    {
        if (Form::count() == 0) {
            Artisan::call('v2-custom-form-update-data');
        }

        $organisation = Organisation::factory()->create();
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['organisation_id' => $organisation->id]);

        $form = Form::first();
        $uri = '/api/v2/forms/' . $form->uuid;

        $this->actingAs($user)
            ->getJson($uri)
            ->assertSuccessful();

        $this->actingAs($admin)
            ->getJson($uri)
            ->assertSuccessful();

        $this->actingAs($user)
            ->getJson($uri . '?lang=fr')
            ->assertSuccessful();
    }
}
