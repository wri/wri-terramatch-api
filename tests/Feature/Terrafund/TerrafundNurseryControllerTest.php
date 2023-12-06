<?php

namespace Tests\Feature\Terrafund;

use App\Models\Framework;
use App\Models\Organisation;
use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TerrafundNurseryControllerTest extends TestCase
{
    use RefreshDatabase;

    private function nurseryData($overrides = [])
    {
        return array_merge(
            [
                'name' => 'test name',
                'start_date' => '2000-01-01',
                'end_date' => '2038-01-28',
                'seedling_grown' => 12345,
                'planting_contribution' => 'the planting contribution',
                'nursery_type' => 'expanding',
            ],
            $overrides
        );
    }

    public function testCreateAction(): void
    {
        $organisation = Organisation::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);

        $this->postJson(
            '/api/terrafund/nursery',
            $this->nurseryData([
                'terrafund_programme_id' => $terrafundProgramme->id,
            ]),
            $this->getHeadersForUser($user->email_address),
        )
        ->assertStatus(201)
        ->assertJsonFragment(
            $this->nurseryData([
                'terrafund_programme_id' => $terrafundProgramme->id,
            ]),
        );
    }

    public function testUpdateAction(): void
    {
        $organisation = Organisation::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $terrafundNursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme,
        ]);
        $user = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);

        $this->actingAs($user)
            ->patchJson(
                '/api/terrafund/nursery/' . $terrafundNursery->id,
                $this->nurseryData(),
            )
            ->assertStatus(200)
            ->assertJsonFragment(
                $this->nurseryData([
                    'id' => $terrafundNursery->id,
                ]),
            );
    }

    public function testUpdateActionRequiresAccess(): void
    {
        $organisation = Organisation::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $terrafundNursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme,
        ]);
        $user = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
        $user->frameworks()->attach($terrafundProgramme->framework_id);

        $this->actingAs($user)
            ->patchJson(
                '/api/terrafund/nursery/' . $terrafundNursery->id,
                $this->nurseryData(),
            )
            ->assertStatus(403);
    }

    public function testReadMyNurseriesAction(): void
    {
        Carbon::setTestNow(Carbon::create(2021, 05, 15));

        $organisation = Organisation::factory()->create();
        $terrafundFramework = Framework::factory()->create([
            'name' => 'Terrafund',
        ]);
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $terrafundNursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $missingNursery = TerrafundNursery::factory()->create();
        $user = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
        $user->frameworks()->attach($terrafundFramework->id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);

        Carbon::setTestNow();

        $dueSubmission = TerrafundDueSubmission::factory()->create([
            'terrafund_due_submissionable_type' => TerrafundNursery::class,
            'terrafund_due_submissionable_id' => $terrafundNursery->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/terrafund/my/nurseries')
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $terrafundNursery->id])
            ->assertJsonMissingExact(['id' => $missingNursery->id])
            ->assertJsonPath('data.0.next_due_submission_id',  $dueSubmission->id)
            ->assertJsonPath('data.0.next_due_submission_due_at',   $dueSubmission->due_at->toISOString());
    }
}
