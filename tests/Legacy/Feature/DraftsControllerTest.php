<?php

namespace Tests\Legacy\Feature;

use App\Models\Programme;
use App\Models\Site;
use App\Models\SiteSubmission;
use App\Models\Submission;
use Illuminate\Support\Facades\Queue;
use Tests\Legacy\LegacyTestCase;

final class DraftsControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $data = [
            'name' => 'Example Draft',
            'type' => 'offer',
        ];
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->postJson('/api/drafts', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'name',
                'is_from_mobile',
                'type',
                'data',
                'created_at',
                'created_by',
                'updated_at',
                'updated_by',
            ],
        ]);
    }

    public function testCreateActionFromMobile(): void
    {
        $data = [
            'name' => 'Example Draft',
            'type' => 'offer',
            'is_from_mobile' => true,
        ];
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->postJson('/api/drafts', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'name',
                'is_from_mobile',
                'type',
                'data',
                'created_at',
                'created_by',
                'updated_at',
                'updated_by',
            ],
        ]);
    }

    public function testCreateActionWithDueSubmission(): void
    {
        $data = [
            'name' => 'Assigning draft to due submission',
            'type' => 'site_submission',
            'due_submission_id' => 2,
        ];
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->postJson('/api/drafts', $data, $headers);
        $response->assertStatus(201);
        $this->assertDatabaseHas('drafts', [
            'id' => $response['data']['id'],
            'due_submission_id' => 2,
        ]);
    }

    public function testMergeAction(): void
    {
        $data = [
            'draft_ids' => [7, 9],
            'type' => 'site_submission',
        ];
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/merge', $data, $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'name',
                'type',
                'data',
                'created_at',
                'created_by',
                'updated_at',
                'updated_by',
            ],
        ]);
        $this->assertDatabaseHas('drafts', [
            'id' => 7,
            'is_merged' => true,
        ]);
        $this->assertDatabaseMissing('drafts', [
            'id' => 9,
        ]);
    }

    public function testMergeActionRequiresMatchingDraftTypes(): void
    {
        $data = [
            'draft_ids' => [7, 8],
            'type' => 'site_submission',
        ];
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/merge', $data, $headers);
        $response->assertStatus(422);
    }

    public function testReadAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/drafts/1', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'name',
                'type',
                'data',
                'created_at',
                'created_by',
                'updated_at',
                'updated_by',
            ],
        ]);
    }

    public function testReadAllByTypeAction(): void
    {
        $this->callReadAllByTypeActionAsOffer();
        $this->callReadAllByTypeActionAsPitch();
        $this->callReadAllByTypeActionAsProgramme();
        $this->callReadAllByTypeActionAsSite();
        $this->callReadAllByTypeActionAsSiteSubmission();
        $this->callReadAllByTypeActionAsProgrammeSubmission();
        $this->callReadAllByTypeActionAsTerrafundProgramme();
        $this->callReadAllByTypeActionAsTerrafundNursery();
        $this->callReadAllByTypeActionAsTerrafundSite();
        $this->callReadAllByTypeActionAsOrganisation();
        $this->callReadAllByTypeActionAsTerrafundNurserySubmission();
        $this->callReadAllByTypeActionAsTerrafundSiteSubmission();
        $this->callReadAllByTypeActionAsTerrafundProgrammeSubmission();
    }

    private function callReadAllByTypeActionAsOffer()
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/drafts/offers', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'data',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                ],
            ],
        ]);
    }

    private function callReadAllByTypeActionAsPitch()
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/drafts/pitches', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'data',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                ],
            ],
        ]);
    }

    private function callReadAllByTypeActionAsProgramme()
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/drafts/programmes', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'data',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                ],
            ],
        ]);
    }

    private function callReadAllByTypeActionAsSite()
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/drafts/sites', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'data',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                ],
            ],
        ]);
    }

    private function callReadAllByTypeActionAsSiteSubmission()
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/drafts/site_submissions', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'data',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                ],
            ],
        ]);
    }

    private function callReadAllByTypeActionAsProgrammeSubmission()
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/drafts/programme_submissions', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'data',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                ],
            ],
        ]);
    }

    private function callReadAllByTypeActionAsTerrafundProgramme()
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/drafts/terrafund_programmes', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'data',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                ],
            ],
        ]);
    }

    private function callReadAllByTypeActionAsTerrafundNursery()
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/drafts/terrafund_nurserys', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'data',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                ],
            ],
        ]);
    }

    private function callReadAllByTypeActionAsTerrafundNurserySubmission()
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/drafts/terrafund_nursery_submissions', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'data',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                ],
            ],
        ]);
    }

    private function callReadAllByTypeActionAsTerrafundSiteSubmission()
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/drafts/terrafund_site_submissions', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'data',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                ],
            ],
        ]);
    }

    private function callReadAllByTypeActionAsTerrafundProgrammeSubmission()
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/drafts/terrafund_programme_submissions', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'data',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                ],
            ],
        ]);
    }

    private function callReadAllByTypeActionAsTerrafundSite()
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->getJson('/api/drafts/terrafund_sites', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'data',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                ],
            ],
        ]);
    }

    private function callReadAllByTypeActionAsOrganisation()
    {
        $headers = $this->getHeaders('terrafund.orphan@example.com', 'Password123');

        $response = $this->getJson('/api/drafts/organisations', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'organisation_id',
                    'name',
                    'type',
                    'data',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                ],
            ],
        ]);
    }

    public function testUpdateAction(): void
    {
        $data = [
            [
                'op' => 'add',
                'path' => '/offer/name',
                'value' => 'foo',
            ],
        ];
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/1', $data, $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'id',
                'organisation_id',
                'name',
                'type',
                'data',
                'created_at',
                'created_by',
                'updated_at',
                'updated_by',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'data' => [
                    'offer' => [
                        'name' => 'foo',
                    ],
                ],
            ],
        ]);
    }

    public function testDeleteAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->deleteJson('/api/drafts/1', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJson([
            'data' => [],
        ]);
    }

    public function testSiteSubmissionFlowWithUploadAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->assertDatabaseHas('drafts', [
            'id' => 7,
            'due_submission_id' => 2,
        ]);

        Queue::fake();

        $uploadResponse1 = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $uploadResponse2 = $this->post('/api/uploads', [
            'upload' => $this->fakeImage(),
        ], $headers);

        $input = [
                [
                    'op' => 'replace',
                    'path' => '/progress/direct_seeding_skipped',
                    'value' => false,
                ],
                [
                    'op' => 'add',
                    'path' => '/document_files/0',
                    'value' => [
                            'upload' => $uploadResponse2->json('data.id'),
                            'title' => 'test2 image file',
                            'collection' => 'testing',
                            'is_public' => false,
                    ],
                ],
                [
                    'op' => 'add',
                    'path' => '/document_files/1',
                    'value' => [
                            'upload' => $uploadResponse1->json('data.id'),
                            'title' => 'test1 csv file',
                            'collection' => 'testing',
                            'is_public' => false,
                    ],
                ],
        ];

        $response = $this->patchJson('/api/drafts/7', $input, $headers)
            ->assertStatus(200);

        $response = $this->patchJson('/api/drafts/7/publish', $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'site_submission_id',
            ],
        ]);

        $siteSubmission = SiteSubmission::with('documentFiles')->find($response->json('data.site_submission_id'));

        $this->assertEquals(2, $siteSubmission->documentFiles->count());
    }

    public function testSiteFlowWithUploadAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->assertDatabaseHas('drafts', [
            'id' => 6,
        ]);

        Queue::fake();

        $uploadResponse1 = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $uploadResponse2 = $this->post('/api/uploads', [
            'upload' => $this->fakeImage(),
        ], $headers);

        $input = [
            [
                'op' => 'replace',
                'path' => '/progress/invasives_skipped',
                'value' => false,
            ],
            [
                'op' => 'add',
                'path' => '/document_files/0',
                'value' => [
                    'upload' => $uploadResponse2->json('data.id'),
                    'title' => 'test2 image file',
                    'collection' => 'testing',
                    'is_public' => false,
                ],
            ],
            [
                'op' => 'add',
                'path' => '/document_files/1',
                'value' => [
                    'upload' => $uploadResponse1->json('data.id'),
                    'title' => 'test1 csv file',
                    'collection' => 'testing',
                    'is_public' => false,
                ],
            ],
        ];

        $this->patchJson('/api/drafts/6', $input, $headers)
            ->assertStatus(200);

        $response = $this->patchJson('/api/drafts/6/publish', $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'site_id',
            ],
        ]);

        $site = Site::with('documentFiles')->find($response->json('data.site_id'));

        $this->assertEquals(2, $site->documentFiles->count());
    }

    public function testProgrammeSubmissionFlowWithUploadAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->assertDatabaseHas('drafts', [
            'id' => 8,
            'due_submission_id' => 1,
        ]);

        Queue::fake();

        $uploadResponse1 = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $uploadResponse2 = $this->post('/api/uploads', [
            'upload' => $this->fakeImage(),
        ], $headers);

        $input = [
            [
                'op' => 'add',
                'path' => '/document_files',
                'value' => [
                    [
                        'upload' => $uploadResponse1->json('data.id'),
                        'title' => 'test1 csv file',
                        'collection' => 'testing',
                        'is_public' => false,
                    ],
                    [
                        'upload' => $uploadResponse2->json('data.id'),
                        'title' => 'test2 image file',
                        'collection' => 'testing',
                        'is_public' => false,
                    ],
                ],
            ],
        ];

        $this->patchJson('/api/drafts/8', $input, $headers)
            ->assertStatus(200);

        $response = $this->patchJson('/api/drafts/8/publish', $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'programme_submission_id',
            ],
        ]);

        $submission = Submission::with('documentFiles')->find($response->json('data.programme_submission_id'));

        $this->assertEquals(2, $submission->documentFiles->count());
    }

    public function testProgrammeFlowWithUploadAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->assertDatabaseHas('drafts', [
            'id' => 5,
        ]);

        Queue::fake();

        $uploadResponse1 = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $uploadResponse2 = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $input = [
            [
                'op' => 'add',
                'path' => '/document_files',
                'value' => [
                    [
                        'upload' => $uploadResponse1->json('data.id'),
                        'title' => 'test1 csv file',
                        'collection' => 'testing',
                        'is_public' => false,
                    ],
                    [
                        'upload' => $uploadResponse2->json('data.id'),
                        'title' => 'test2 csv file',
                        'collection' => 'testing',
                        'is_public' => false,
                    ],
                ],
            ],
        ];

        $this->patchJson('/api/drafts/5', $input, $headers);

        $response = $this->patchJson('/api/drafts/5/publish', $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'programme_id',
            ],
        ]);

        $programme = Programme::with('documentFiles')->find($response->json('data.programme_id'));

        $this->assertEquals(2, $programme->documentFiles->count());
    }

    public function testProgrammeAdditionalTreeSpeciesUploadAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->assertDatabaseHas('drafts', [
            'id' => 5,
        ]);

        Queue::fake();

        $uploadResponse1 = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $input = [
            [
                'op' => 'add',
                'path' => '/additional_tree_species',
                'value' => $uploadResponse1->json('data.id'),
            ],
        ];

        $this->patchJson('/api/drafts/5', $input, $headers)
            ->assertStatus(200);

        $response = $this->patchJson('/api/drafts/5/publish', $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'programme_id',
            ],
        ]);
        //        $programme = Programme::with('documentFiles')->find($response->json('data.programme_id'));
        //        dump(new ProgrammeResource($programme));
        //
        //        $siteSubmission = SiteSubmission::with('documentFiles')->find($response->json('data.site_submission_id'));
    }

    public function testProgrammeSubmissionAdditionalTreeSpeciesUploadAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->assertDatabaseHas('drafts', [
            'id' => 8,
        ]);

        Queue::fake();

        $uploadResponse1 = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $input = [
            [
                'op' => 'add',
                'path' => '/additional_tree_species',
                'value' => $uploadResponse1->json('data.id'),
            ],
        ];

        $this->patchJson('/api/drafts/8', $input, $headers)
            ->assertStatus(200);

        $response = $this->patchJson('/api/drafts/8/publish', $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'programme_submission_id',
            ],
        ]);
    }

    public function testSiteAdditionalTreeSpeciesUploadAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->assertDatabaseHas('drafts', [
            'id' => 6,
        ]);

        Queue::fake();

        $uploadResponse1 = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $input = [
            [
                'op' => 'add',
                'path' => '/additional_tree_species',
                'value' => $uploadResponse1->json('data.id'),
            ],
        ];

        $this->patchJson('/api/drafts/6', $input, $headers)
            ->assertStatus(200);

        $response = $this->patchJson('/api/drafts/6/publish', $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'site_id',
            ],
        ]);
    }

    public function testSiteSubmissionAdditionalTreeSpeciesUploadAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->assertDatabaseHas('drafts', [
            'id' => 9,
        ]);

        Queue::fake();

        $uploadResponse1 = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $input = [
            [
                'op' => 'add',
                'path' => '/additional_tree_species',
                'value' => $uploadResponse1->json('data.id'),
            ],
        ];

        $this->patchJson('/api/drafts/9', $input, $headers)
            ->assertStatus(200);

        $response = $this->patchJson('/api/drafts/9/publish', $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'site_submission_id',
            ],
        ]);
    }

    public function testPublishAction(): void
    {
        $this->callPublishActionAsOffer();
        $this->callPublishActionAsPitch();
        $this->callPublishActionAsProgramme();
        $this->callPublishActionAsSite();
        $this->callPublishActionAsSiteSubmission();
        $this->callPublishActionAsProgrammeSubmission();
        $this->callPublishActionAsTerrafundProgramme();
        $this->callPublishActionAsTerrafundNursery();
        $this->callPublishActionAsTerrafundSite();
        $this->callPublishActionAsOrganisation();
        $this->callPublishActionAsTerrafundNurserySubmission();
        $this->callPublishActionAsTerrafundSiteSubmission();
        $this->callPublishActionAsTerrafundProgrammeSubmission();
    }

    private function callPublishActionAsOffer()
    {
        $data = [];
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/3/publish', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'offer_id',
            ],
        ]);
    }

    private function callPublishActionAsPitch()
    {
        $data = [];
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/4/publish', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'pitch_id',
            ],
        ]);
    }

    private function callPublishActionAsProgramme()
    {
        $data = [];
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/5/publish', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'programme_id',
            ],
        ]);
    }

    private function callPublishActionAsSite()
    {
        $data = [];
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/6/publish', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'site_id',
            ],
        ]);
    }

    private function callPublishActionAsSiteSubmission()
    {
        $data = [];
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->assertDatabaseHas('drafts', [
            'id' => 7,
            'due_submission_id' => 2,
        ]);

        $this->assertDatabaseHas('due_submissions', [
            'id' => 2,
            'is_submitted' => false,
        ]);

        $response = $this->patchJson('/api/drafts/7/publish', $data, $headers);

        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'site_submission_id',
            ],
        ]);

        $siteSubmission = SiteSubmission::find($response['data']['site_submission_id']);

        $this->assertSame(2, $siteSubmission->due_submission_id);

        $this->assertDatabaseMissing('drafts', [
            'id' => 7,
            'due_submission_id' => 2,
        ]);
        $this->assertDatabaseHas('due_submissions', [
            'id' => 2,
            'is_submitted' => true,
        ]);
    }

    private function callPublishActionAsProgrammeSubmission()
    {
        $data = [];
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/8/publish', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'programme_submission_id',
            ],
        ]);

        $this->assertDatabaseMissing('drafts', [
            'id' => 8,
            'due_submission_id' => 1,
        ]);
    }

    private function callPublishActionAsTerrafundProgramme()
    {
        $data = [];
        $headers = $this->getHeaders('terrafund@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/11/publish', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'terrafund_programme_id',
            ],
        ]);
    }

    private function callPublishActionAsTerrafundNursery()
    {
        $data = [];
        $headers = $this->getHeaders('terrafund@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/12/publish', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'terrafund_nursery_id',
            ],
        ]);
    }

    private function callPublishActionAsTerrafundNurserySubmission()
    {
        $data = [];
        $headers = $this->getHeaders('terrafund@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/15/publish', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'terrafund_nursery_submission_id',
            ],
        ]);
    }

    private function callPublishActionAsTerrafundSiteSubmission()
    {
        $data = [];
        $headers = $this->getHeaders('terrafund@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/16/publish', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'terrafund_site_submission_id',
            ],
        ]);
    }

    private function callPublishActionAsTerrafundProgrammeSubmission()
    {
        $data = [];
        $headers = $this->getHeaders('terrafund@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/17/publish', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'terrafund_programme_submission_id',
            ],
        ]);
    }

    private function callPublishActionAsTerrafundSite()
    {
        $data = [];
        $headers = $this->getHeaders('terrafund@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/14/publish', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'terrafund_site_id',
            ],
        ]);
    }

    private function callPublishActionAsOrganisation()
    {
        $data = [];
        $headers = $this->getHeaders('terrafund.orphan@example.com', 'Password123');

        $response = $this->patchJson('/api/drafts/13/publish', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            'data' => [
                'organisation_id',
            ],
        ]);
    }
}
