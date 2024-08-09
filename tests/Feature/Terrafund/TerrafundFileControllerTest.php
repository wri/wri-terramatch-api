<?php

namespace Tests\Feature\Terrafund;

use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\Upload;
use App\Models\V2\User;
use Tests\TestCase;

/**
 * Some tests for terrafund files are in the legacy
 * version of this test
 */
final class TerrafundFileControllerTest extends TestCase
{
    private function prepareSiteSubmissionBulkFiles(int $numberOfFiles, User $owner, TerrafundSiteSubmission $submission, array $overrides)
    {
        $files = Upload::factory()
            ->count($numberOfFiles) // This can be the number of files
            ->create(['user_id' => $owner->id])
            ->map(function (Upload $file) use ($submission, $overrides) {
                return array_merge([
                    'fileable_type' => 'site_submission',
                    'fileable_id' => $submission->id,
                    'upload' => $file->id,
                    'is_public' => false,
                ], $overrides);
            })
            ->toArray();

        return ['data' => $files];
    }

    public function testCreateAction(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $nursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $nurserySubmission = TerrafundNurserySubmission::factory()->create([
            'terrafund_nursery_id' => $nursery->id,
        ]);
        $file = Upload::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson('/api/terrafund/file', [
                'fileable_type' => 'nursery_submission',
                'fileable_id' => $nurserySubmission->id,
                'upload' => $file->id,
                'is_public' => false,
                'location_lat' => 38.8951,
                'location_long' => -77.0364,
            ])
            ->assertStatus(201)
            ->assertJsonFragment([
                'fileable_type' => TerrafundNurserySubmission::class,
                'fileable_id' => $nurserySubmission->id,
                'is_public' => false,
                'location_lat' => 38.8951,
                'location_long' => -77.0364,
            ]);
    }

    public function testCreateActionNurserySubmissionRequiresImage(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $nursery = TerrafundNursery::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $nurserySubmission = TerrafundNurserySubmission::factory()->create([
            'terrafund_nursery_id' => $nursery->id,
        ]);
        $file = Upload::factory()->pdf()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson('/api/terrafund/file', [
                'fileable_type' => 'nursery_submission',
                'fileable_id' => $nurserySubmission->id,
                'upload' => $file->id,
                'is_public' => false,
            ])
            ->assertStatus(422);
    }

    public function testCreateActionRequiresAccessToNurserySubmission(): void
    {
        $user = User::factory()->create();
        $nurserySubmission = TerrafundNurserySubmission::factory()->create();
        $file = Upload::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson('/api/terrafund/file', [
                'fileable_type' => 'nursery_submission',
                'fileable_id' => $nurserySubmission->id,
                'upload' => $file->id,
                'is_public' => false,
            ])
            ->assertStatus(403);
    }

    public function testCreateActionForProgramme(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $programmeSubmission = TerrafundProgrammeSubmission::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $file = Upload::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson('/api/terrafund/file', [
                'fileable_type' => 'programme_submission',
                'collection' => 'photos',
                'fileable_id' => $programmeSubmission->id,
                'upload' => $file->id,
                'is_public' => false,
            ])
            ->assertStatus(201)
            ->assertJsonFragment([
                'fileable_type' => TerrafundProgrammeSubmission::class,
                'fileable_id' => $programmeSubmission->id,
                'is_public' => false,
                'type' => 'image',
            ]);
    }

    public function testCreateActionProgrammeSubmissionRequiresImage(): void
    {
        $user = User::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $programmeSubmission = TerrafundProgrammeSubmission::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $file = Upload::factory()->pdf()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson('/api/terrafund/file', [
                'fileable_type' => 'programme_submission',
                'fileable_id' => $programmeSubmission->id,
                'upload' => $file->id,
                'is_public' => false,
            ])
            ->assertStatus(422);
    }

    public function testCreateActionRequiresAccessToProgrammeSubmission(): void
    {
        $user = User::factory()->create();
        $programmeSubmission = TerrafundProgrammeSubmission::factory()->create();
        $file = Upload::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson('/api/terrafund/file', [
                'fileable_type' => 'programme_submission',
                'fileable_id' => $programmeSubmission->id,
                'upload' => $file->id,
                'is_public' => false,
            ])
            ->assertStatus(403);
    }

    public function testCreateBulkAction(): void
    {
        $user = User::factory()->create();

        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);
        $data = $this->prepareSiteSubmissionBulkFiles(12, $user, $siteSubmission, [ ]);

        $this->actingAs($user)
            ->postJson('/api/terrafund/file/bulk', $data, $this->getHeadersForUser($user->email_address))
            ->assertCreated()
            ->assertJsonCount(12, 'data');
    }

    public function testCreateBulkActionTooFew(): void
    {
        $user = User::factory()->create();

        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);
        $data = $this->prepareSiteSubmissionBulkFiles(9, $user, $siteSubmission, [ ]);

        $this->actingAs($user)
            ->postJson('/api/terrafund/file/bulk', $data, $this->getHeadersForUser($user->email_address))
            ->assertStatus(422);
    }

    public function testCreateBulkActionWrongType(): void
    {
        $user = User::factory()->create();

        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $user->frameworks()->attach($terrafundProgramme->framework_id);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);
        $site = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $siteSubmission = TerrafundSiteSubmission::factory()->create([
            'terrafund_site_id' => $site->id,
        ]);
        $data = $this->prepareSiteSubmissionBulkFiles(9, $user, $siteSubmission, [ 'fileable_type' => 'nursery_submission']);

        $this->actingAs($user)
            ->postJson('/api/terrafund/file/bulk', $data, $this->getHeadersForUser($user->email_address))
            ->assertStatus(422);
    }
}
