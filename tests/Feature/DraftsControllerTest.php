<?php

namespace Tests\Feature;

use App\Models\Draft;
use App\Models\DueSubmission;
use App\Models\Programme;
use App\Models\Site;
use App\Models\User;
use Tests\TestCase;

final class DraftsControllerTest extends TestCase
{
    /** @group slow */
    public function testReadAllByTypeActionWhereUserCreatedDraft()
    {
        $user = User::factory()->create();

        Draft::factory()->count(5)->create([
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson(
                '/api/drafts/terrafund_programmes'
            );
        $response
            ->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function testReadAllByTypeActionWhereOrganisationCreatedDraft(): void
    {
        $user = User::factory()->create();
        $orgUser = User::factory()->create([
            'organisation_id' => $user->organisation_id,
        ]);

        Draft::factory()->count(5)->create([
            'created_by' => $orgUser->id,
            'organisation_id' => $orgUser->organisation_id,
        ]);

        $response = $this->actingAs($user)
            ->getJson(
                '/api/drafts/terrafund_programmes'
            );
        $response
            ->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function testReadAllByTypeActionWhereUserOrOrganisationCreatedDraft(): void
    {
        $user = User::factory()->create();
        $orgUser = User::factory()->create([
            'organisation_id' => $user->organisation_id,
        ]);

        Draft::factory()->count(5)->create([
            'created_by' => $orgUser->id,
            'organisation_id' => $orgUser->organisation_id,
        ]);
        Draft::factory()->count(5)->create([
            'created_by' => $user->id,
            'organisation_id' => $orgUser->organisation_id,
        ]);

        $response = $this->actingAs($user)
            ->getJson(
                '/api/drafts/terrafund_programmes'
            );
        $response
            ->assertStatus(200)
            ->assertJsonCount(10, 'data');
    }

    public function testReadAllByTypeActionWhereUserIsInPPCProgramme(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $programme = Programme::factory()->create([
            'organisation_id' => $otherUser->organisation_id,
        ]);

        $user->programmes()->sync($programme->id);

        $dueSubmission = DueSubmission::factory()->create([
            'due_submissionable_id' => $programme->id,
        ]);

        Draft::factory()->count(5)->create([
            'created_by' => $otherUser->id,
            'organisation_id' => $otherUser->organisation_id,
            'due_submission_id' => $dueSubmission->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson(
                '/api/drafts/terrafund_programmes'
            );
        $response
            ->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function testReadAllByTypeActionWhereUserIsInPPCSite(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $programme = Programme::factory()->create([
            'organisation_id' => $otherUser->organisation_id,
        ]);
        $site = Site::factory()->create([
            'programme_id' => $programme->id,
        ]);

        $user->programmes()->sync($programme->id);

        $dueSubmission = DueSubmission::factory()->create([
            'due_submissionable_type' => Site::class,
            'due_submissionable_id' => $site->id,
        ]);

        Draft::factory()->count(5)->create([
            'created_by' => $otherUser->id,
            'organisation_id' => $otherUser->organisation_id,
            'due_submission_id' => $dueSubmission->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson(
                '/api/drafts/terrafund_programmes'
            );
        $response
            ->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function testUpdateActionWhereUserIsInPPCProgramme(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $programme = Programme::factory()->create([
            'organisation_id' => $otherUser->organisation_id,
        ]);

        $user->programmes()->sync($programme->id);

        $dueSubmission = DueSubmission::factory()->create([
            'due_submissionable_id' => $programme->id,
        ]);

        $draft = Draft::factory()->create([
            'created_by' => $otherUser->id,
            'organisation_id' => $otherUser->organisation_id,
            'due_submission_id' => $dueSubmission->id,
        ]);

        $response = $this->actingAs($user)
            ->patchJson(
                '/api/drafts/' . $draft->id,
                []
            );
        $response->assertStatus(200);
    }

    public function testUpdateActionWhereUserIsInPPCSite(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $programme = Programme::factory()->create([
            'organisation_id' => $otherUser->organisation_id,
        ]);
        $site = Site::factory()->create([
            'programme_id' => $programme->id,
        ]);

        $user->programmes()->sync($programme->id);

        $dueSubmission = DueSubmission::factory()->create([
            'due_submissionable_type' => Site::class,
            'due_submissionable_id' => $site->id,
        ]);

        $draft = Draft::factory()->create([
            'created_by' => $otherUser->id,
            'organisation_id' => $otherUser->organisation_id,
            'due_submission_id' => $dueSubmission->id,
        ]);

        $response = $this->actingAs($user)
            ->patchJson(
                '/api/drafts/' . $draft->id,
                []
            );
        $response->assertStatus(200);
    }

    public function testMergeActionWhereUserIsInPPCProgramme(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $programme = Programme::factory()->create([
            'organisation_id' => $otherUser->organisation_id,
        ]);

        $user->programmes()->sync($programme->id);

        $dueSubmission = DueSubmission::factory()->create([
            'due_submissionable_id' => $programme->id,
        ]);

        $draft = Draft::factory()->programmeSubmission()->create([
            'created_by' => $otherUser->id,
            'organisation_id' => $otherUser->organisation_id,
            'due_submission_id' => $dueSubmission->id,
        ]);
        $userDraft = Draft::factory()->programmeSubmission()->create([
            'created_by' => $user->id,
            'organisation_id' => $user->organisation_id,
        ]);

        $response = $this->actingAs($user)
            ->patchJson(
                '/api/drafts/merge',
                [
                    'type' => 'programme_submission',
                    'draft_ids' => [
                        $draft->id, $userDraft->id,
                    ],
                ]
            );
        $response->assertStatus(200);
    }

    public function testMergeActionWhereUserIsInPPCSite(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $programme = Programme::factory()->create([
            'organisation_id' => $otherUser->organisation_id,
        ]);
        $site = Site::factory()->create([
            'programme_id' => $programme->id,
        ]);

        $user->programmes()->sync($programme->id);

        $dueSubmission = DueSubmission::factory()->create([
            'due_submissionable_type' => Site::class,
            'due_submissionable_id' => $site->id,
        ]);

        $draft = Draft::factory()->siteSubmission()->create([
            'created_by' => $otherUser->id,
            'organisation_id' => $otherUser->organisation_id,
            'due_submission_id' => $dueSubmission->id,
        ]);
        $userDraft = Draft::factory()->siteSubmission()->create([
            'created_by' => $user->id,
            'organisation_id' => $user->organisation_id,
        ]);

        $response = $this->actingAs($user)
            ->patchJson(
                '/api/drafts/merge',
                [
                    'type' => 'site_submission',
                    'draft_ids' => [
                        $draft->id, $userDraft->id,
                    ],
                ]
            );
        $response->assertStatus(200);
    }
}
