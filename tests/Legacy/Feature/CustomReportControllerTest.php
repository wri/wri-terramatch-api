<?php

namespace Tests\Legacy\Feature;

use App\Models\Reporting\ControlSiteReport;
use App\Models\Reporting\ControlSiteSubmissionReport;
use App\Models\Reporting\ProgrammeReport;
use App\Models\Reporting\ProgrammeSubmissionReport;
use App\Models\Reporting\SiteReport;
use App\Models\Reporting\SiteSubmissionReport;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Group;
use Tests\Legacy\LegacyTestCase;

final class CustomReportControllerTest extends LegacyTestCase
{
    public function testFetchAvailableFieldsSiteAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/exports/site/field_list', $headers)
            ->assertJsonFragment(['data' => array_merge(SiteReport::AVAILABLE_FIELDS, SiteReport::AVAILABLE_FIlES) ])
            ->assertStatus(200);
    }

    public function testFetchAvailableFieldsControlSiteAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/exports/control_site/field_list', $headers)
            ->assertStatus(200)
            ->assertJsonFragment(['data' => array_merge(ControlSiteReport::AVAILABLE_FIELDS, ControlSiteReport::AVAILABLE_FIlES) ]);
    }

    public function testFetchAvailableFieldsControlSiteSubmissionAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/exports/control_site_submission/field_list', $headers)
            ->assertJsonFragment(['data' => array_merge(ControlSiteSubmissionReport::AVAILABLE_FIELDS, ControlSiteSubmissionReport::AVAILABLE_FIlES) ])
            ->assertStatus(200);
    }

    public function testFetchAvailableFieldsProgrammeAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/exports/programme/field_list', $headers)
            ->assertJsonFragment(['data' => array_merge(ProgrammeReport::AVAILABLE_FIELDS, ProgrammeReport::AVAILABLE_FIlES) ])
            ->assertStatus(200);
    }

    public function testFetchAvailableFieldsSubmissionAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/exports/submission/field_list', $headers)
            ->assertJsonFragment(['data' => array_merge(ProgrammeSubmissionReport::AVAILABLE_FIELDS, ProgrammeSubmissionReport::AVAILABLE_FIlES) ])
            ->assertStatus(200);
    }

    public function testFetchAvailableFieldsSiteSubmissionAction(): void
    {
        $headers = $this->getHeaders('steve@example.com', 'Password123');

        $this->getJson('/api/exports/site_submission/field_list', $headers)
            ->assertJsonFragment(['data' => array_merge(SiteSubmissionReport::AVAILABLE_FIELDS, SiteSubmissionReport::AVAILABLE_FIlES) ])
            ->assertStatus(200);
    }

    #[Group('skipPipeline')]
    public function testGenerateCustomSiteReportAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $data = [
            'exportable_type' => 'site',
            'exportable_id' => 1,
            'duration' => 6,
            'format' => 'csv',
            'field_list' => array_keys(array_merge(SiteReport::AVAILABLE_FIELDS, SiteReport::AVAILABLE_FIlES)),
        ];

        $this->postJson('/api/exports/custom', $data, $headers)
            ->assertStatus(200);
    }

    #[Group('skipPipeline')]
    public function testGenerateCustomSiteSubmissionReportWithDocumentFilesAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        Queue::fake();

        $uploadResponse = $this->post('/api/uploads', [
            'upload' => $this->fakeValidCsv(),
        ], $headers);

        $this->postJson('/api/document_files/file', [
            'document_fileable_id' => 1,
            'document_fileable_type' => 'site_submission',
            'upload' => $uploadResponse->json('data.id'),
            'title' => 'file for export test',
            'collection' => 'to-be-deleted',
            'is_public' => false,
        ], $headers)
            ->assertStatus(201);


        $data = [
            'exportable_type' => 'site_submission',
            'exportable_id' => 1,
            'duration' => 6,
            'format' => 'csv',
            'field_list' => array_keys(array_merge(SiteSubmissionReport::AVAILABLE_FIELDS, SiteSubmissionReport::AVAILABLE_FIlES)),
        ];

        $this->postJson('/api/exports/custom', $data, $headers)
            ->assertStatus(200);
    }

    #[Group('skipPipeline')]
    public function testGenerateCustomProgrammeReportAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $data = [
            'exportable_type' => 'programme',
            'exportable_id' => 1,
            'format' => 'csv',
            'field_list' => array_keys(array_merge(ProgrammeReport::AVAILABLE_FIELDS, ProgrammeReport::AVAILABLE_FIlES)),
        ];

        $this->postJson('/api/exports/custom', $data, $headers)
            ->assertStatus(200);
    }

    #[Group('skipPipeline')]
    public function testGenerateCustomProgrammeSubmissionReportAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $data = [
            'exportable_type' => 'submission',
            'exportable_id' => 1,
            'format' => 'csv',
            'field_list' => array_keys(array_merge(ProgrammeSubmissionReport::AVAILABLE_FIELDS, ProgrammeSubmissionReport::AVAILABLE_FIlES)),
        ];

        $this->postJson('/api/exports/custom', $data, $headers)
            ->assertStatus(200);
    }

    public function testGenerateCustomSiteNoFilesReportAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $data = [
            'exportable_type' => 'site',
            'exportable_id' => 1,
            'duration' => 6,
            'format' => 'csv',
            'field_list' => array_keys(SiteReport::AVAILABLE_FIELDS),
        ];

        $this->postJson('/api/exports/custom', $data, $headers)
            ->assertStatus(200);
    }

    public function testGenerateCustomControlSiteNoFilesReportAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $data = [
            'exportable_type' => 'control_site',
            'exportable_id' => 8,
            'duration' => 6,
            'format' => 'csv',
            'field_list' => array_keys(ControlSiteReport::AVAILABLE_FIELDS),
        ];

        $this->postJson('/api/exports/custom', $data, $headers)
            ->assertStatus(200);
    }

    public function testGenerateCustomControlSiteSubmissionNoFilesReportAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $data = [
            'exportable_type' => 'control_site_submission',
            'exportable_id' => 4,
            'duration' => 6,
            'format' => 'csv',
            'field_list' => array_keys(ControlSiteSubmissionReport::AVAILABLE_FIELDS),
        ];

        $this->postJson('/api/exports/custom', $data, $headers)
            ->assertStatus(200);
    }

    public function testGenerateCustomSiteSubmissionNoFilesReportAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $data = [
            'exportable_type' => 'site_submission',
            'exportable_id' => 1,
            'duration' => 6,
            'format' => 'csv',
            'field_list' => array_keys(SiteSubmissionReport::AVAILABLE_FIELDS),
        ];

        $this->postJson('/api/exports/custom', $data, $headers)
            ->assertStatus(200);
    }

    public function testGenerateCustomProgrammeNoFilesReportAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $data = [
            'exportable_type' => 'programme',
            'exportable_id' => 1,
            'format' => 'csv',
            'field_list' => array_keys(ProgrammeReport::AVAILABLE_FIELDS),
        ];

        $this->postJson('/api/exports/custom', $data, $headers)
            ->assertStatus(200);
    }

    public function testGenerateCustomProgrammeSubmissionNoFilesReportAction(): void
    {
        $headers = $this->getHeaders('jane@example.com', 'Password123');

        $data = [
            'exportable_type' => 'submission',
            'exportable_id' => 1,
            'format' => 'csv',
            'field_list' => array_keys(ProgrammeSubmissionReport::AVAILABLE_FIELDS),
        ];

        $this->postJson('/api/exports/custom', $data, $headers)
            ->assertStatus(200);
    }
}
