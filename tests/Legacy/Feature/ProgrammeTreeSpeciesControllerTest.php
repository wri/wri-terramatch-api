<?php

namespace Tests\Legacy\Feature;

use App\Clients\TreeSpeciesClient;
use App\Exceptions\ExternalAPIException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class ProgrammeTreeSpeciesControllerTest extends LegacyTestCase
{
    public function testCreateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->postJson('/api/programme/tree_species', [
            'name' => 'Some tree species',
            'programme_id' => 1,
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'programme_id' => 1,
            'name' => 'Some tree species',
        ]);
    }

    public function testCreateBulkAction()
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson('/api/programme/1/tree_species/bulk', [
            'tree_species' => [
                [
                    'name' => 'tree 1',
                ], [
                    'name' => 'tree 2',
                ],
            ],
        ], $headers)
        ->assertStatus(201);

        $this->assertDatabaseCount('programme_tree_species', 2); // 2 for this submission

        $this->postJson('/api/programme/1/tree_species/bulk', [
            'tree_species' => [],
        ], $headers)
        ->assertStatus(201);

        $this->assertDatabaseCount('programme_tree_species', 0); // 0 for this submission
    }

    public function testCreateBulkForSubmissionAction()
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->assertDatabaseCount('programme_tree_species', 9); // 8 for this submission, 1 elsewhere

        $this->postJson('/api/programme/submission/1/tree_species/bulk', [
            'tree_species' => [
                [
                    'name' => 'tree 1',
                    'amount' => 1,
                ], [
                    'name' => 'tree 2',
                    'amount' => 2,
                ],
            ],
        ], $headers)
        ->assertStatus(201)
        ->assertJsonFragment([
            'name' => 'tree 1',
            'amount' => 1,
        ])
        ->assertJsonFragment([
            'name' => 'tree 2',
            'amount' => 2,
        ]);

        $this->assertDatabaseCount('programme_tree_species', 3); // 2 for this submission, 1 elsewhere

        $this->postJson('/api/programme/submission/1/tree_species/bulk', [
            'tree_species' => [],
        ], $headers)
        ->assertStatus(201);

        $this->assertDatabaseCount('programme_tree_species', 1); // 0 for this submission, 1 elsewhere
    }

    public function testCreateActionNameIsRequired()
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->postJson('/api/programme/tree_species', [
            'programme_id' => 1,
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateActionProgrammeIdIsRequired(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->postJson('/api/programme/tree_species', [
            'name' => 'Some tree species',
        ], $headers)
        ->assertStatus(422);
    }

    public function testCreateActionProgrammeIdHasToExist(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $r = $this->postJson('/api/programme/tree_species', [
            'name' => 'Some tree species',
            'programme_id' => 4565654,
        ], $headers)
        ->assertStatus(422);
    }

    public function testDeleteAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->deleteJson('/api/programme/tree_species/1', $headers)
        ->assertStatus(200);
    }

    public function testDeleteActionRequiresBelongingToTreeSpeciesProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->deleteJson('/api/programme/tree_species/1', $headers)
        ->assertStatus(403);
    }

    public function testReadAllByProgrammeAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->getJson('/api/programme/1/tree_species', $headers)
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => 9,
                'name' => 'A tree species',
                'programme_id' => 1,
            ]);
    }

    public function testReadAllByProgrammeActionRequiresBelongingToTreeSpeciesProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $this->getJson('/api/programme/1/tree_species', $headers)
            ->assertStatus(403);
    }

    public function testReadAllAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];
        $response = $this->getJson('/api/programmes/tree_species', $headers);
        $response->assertStatus(200);
    }

    public function testSearchTreeSpeciesAction(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'tree name',
            ])),
            new Response(200, [], json_encode([])),
            new RequestException('Error Communicating with Server', new Request('GET', 'test')),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $treeSpeciesClient = new TreeSpeciesClient($client);

        $response = $treeSpeciesClient->search('test');
        $this->assertContains('tree name', $response);

        $response = $treeSpeciesClient->search('empty response');
        $this->assertEmpty($response);

        $this->expectException(ExternalAPIException::class);
        $treeSpeciesClient->search('server fail');
    }
}
