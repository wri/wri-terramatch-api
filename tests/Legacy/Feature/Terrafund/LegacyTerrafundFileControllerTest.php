<?php

namespace Tests\Legacy\Feature\Terrafund;

use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class LegacyTerrafundFileControllerTest extends LegacyTestCase
{
    private function uploadFile($token, $file)
    {
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $response = $this->post('/api/uploads', [
            'upload' => $file,
        ], $headers);

        return $response->json('data.id');
    }

    public function testCreateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $uploadId = $this->uploadFile($token, $this->fakeFile());

        $this->postJson('/api/terrafund/file', [
            'fileable_type' => 'programme',
            'fileable_id' => 1,
            'upload' => $uploadId,
            'is_public' => false,
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'fileable_type' => TerrafundProgramme::class,
            'fileable_id' => 1,
            'is_public' => false,
        ]);
    }

    public function testCreateActionForNursery(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $uploadId = $this->uploadFile($token, $this->fakeImage());

        $this->postJson('/api/terrafund/file', [
            'fileable_type' => 'nursery',
            'fileable_id' => 1,
            'upload' => $uploadId,
            'is_public' => false,
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'fileable_type' => TerrafundNursery::class,
            'fileable_id' => 1,
            'is_public' => false,
        ]);
    }

    public function testCreateActionForSite(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $uploadId = $this->uploadFile($token, $this->fakeImage());

        $this->postJson('/api/terrafund/file', [
            'fileable_type' => 'site',
            'fileable_id' => 1,
            'upload' => $uploadId,
            'is_public' => false,
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'fileable_type' => TerrafundSite::class,
            'fileable_id' => 1,
            'is_public' => false,
        ]);
    }

    public function testCreateActionProgrammeRequiresPdf(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $uploadId = $this->uploadFile($token, $this->fakeMap());

        $this->postJson('/api/terrafund/file', [
            'fileable_type' => 'programme',
            'fileable_id' => 1,
            'upload' => $uploadId,
            'is_public' => false,
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateActionNurseryRequiresImage(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $uploadId = $this->uploadFile($token, $this->fakeFile());

        $this->postJson('/api/terrafund/file', [
            'fileable_type' => 'nursery',
            'fileable_id' => 1,
            'upload' => $uploadId,
            'is_public' => false,
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateActionSiteRequiresImage(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $uploadId = $this->uploadFile($token, $this->fakeFile());

        $this->postJson('/api/terrafund/file', [
            'fileable_type' => 'site',
            'fileable_id' => 1,
            'upload' => $uploadId,
            'is_public' => false,
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateActionRequiresBelongingToProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $uploadId = $this->uploadFile($token, $this->fakeFile());

        $this->postJson('/api/terrafund/file', [
            'fileable_type' => 'programme',
            'fileable_id' => 1,
            'upload' => $uploadId,
            'is_public' => false,
        ], $headers)
        ->assertStatus(403);
    }

    public function testCreateActionRequiresBelongingToNurseryProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $uploadId = $this->uploadFile($token, $this->fakeFile());

        $this->postJson('/api/terrafund/file', [
            'fileable_type' => 'nursery',
            'fileable_id' => 1,
            'upload' => $uploadId,
            'is_public' => false,
        ], $headers)
        ->assertStatus(403);
    }

    public function testCreateActionRequiresBelongingToSiteProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'andrew@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $uploadId = $this->uploadFile($token, $this->fakeImage());

        $this->postJson('/api/terrafund/file', [
            'fileable_type' => 'site',
            'fileable_id' => 1,
            'upload' => $uploadId,
            'is_public' => false,
        ], $headers)
        ->assertStatus(403);
    }

    public function testDeleteAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $uploadId = $this->uploadFile($token, $this->fakeFile());

        $fileId = $this->postJson('/api/terrafund/file', [
            'fileable_type' => 'programme',
            'fileable_id' => 1,
            'upload' => $uploadId,
            'is_public' => false,
        ], $headers)->json('data.id');

        $this->deleteJson('/api/terrafund/file/' . $fileId, [], $headers)
            ->assertStatus(200);
    }
}
