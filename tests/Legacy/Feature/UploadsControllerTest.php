<?php

namespace Tests\Legacy\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class UploadsControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $data = [
            'upload' => $this->fakeImage(),
            'title' => 'test File',
        ];
        $response = $this->post('/api/uploads', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'uploaded_at',
                'title',
            ],
        ]);
        $this->assertDatabaseHas('uploads', ['user_id' => 3]);
    }

    public function testCorruptedImage(): void
    {
        $token = Auth::attempt([
           'email_address' => 'steve@example.com',
           'password' => 'Password123',
       ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $data = [
            'upload' => $this->fakeCorruptedImage(),
        ];
        $response = $this->post('/api/uploads', $data, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(422);
    }

    public function testUploadAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
        $dataA = [
            'upload' => $this->fakeImage(),
            'title' => 'original title',
        ];
        $response = $this->post('/api/uploads', $dataA, $headers);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'uploaded_at',
                'title',
            ],
        ]);

        $uploadId = $response->json('data.id');

        $dataB = [
            'title' => 'title has been Updated',
        ];

        $response = $this->put('/api/uploads/' . $uploadId . '/update', $dataB, $headers);
        $response->assertStatus(200);
        $this->assertEquals($dataB['title'], $response->json('data.title'));
    }
}
