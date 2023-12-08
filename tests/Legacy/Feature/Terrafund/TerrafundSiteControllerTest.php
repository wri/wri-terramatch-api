<?php

namespace Tests\Legacy\Feature\Terrafund;

use App\Models\Organisation;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\Legacy\LegacyTestCase;

final class TerrafundSiteControllerTest extends LegacyTestCase
{
    private function siteData($overrides = [])
    {
        return array_merge(
            [
                'name' => 'test name',
                'start_date' => '2000-01-01',
                'end_date' => '2038-01-28',
                'boundary_geojson' => '{"type":"Polygon","coordinates":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}',
                'restoration_methods' => [
                    'agroforestry',
                    'plantations',
                ],
                'land_tenures' => [
                    'public',
                    'private',
                ],
                'hectares_to_restore' => 10,
                'landscape_community_contribution' => 'community contribution',
                'disturbances' => 'disturbances on the site',
            ],
            $overrides
        );
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

        $this->postJson(
            '/api/terrafund/site',
            $this->siteData([
                'terrafund_programme_id' => 1,
            ]),
            $headers,
        )
        ->assertStatus(201)
        ->assertJsonFragment(
            $this->siteData([
                'terrafund_programme_id' => 1,
            ])
        );
    }

    public function testCreateActionUserMustBeInProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson(
            '/api/terrafund/site',
            $this->siteData([
                'terrafund_programme_id' => 2,
            ]),
            $headers,
        )
        ->assertStatus(403);
    }

    public function testCreateActionStartDateMustBeBeforeEndDate(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson(
            '/api/terrafund/site',
            $this->siteData([
                'terrafund_programme_id' => 1,
                'start_date' => '2000-01-01',
                'end_date' => '1999-01-28',
            ]),
            $headers,
        )
        ->assertStatus(422);
    }

    public function testCreateActionRequiresBeingATerrafundUser(): void
    {
        $token = Auth::attempt([
            'email_address' => 'steve@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->postJson(
            '/api/terrafund/site',
            $this->siteData([
                'terrafund_programme_id' => 1,
            ]),
            $headers,
        )
        ->assertStatus(403);
    }

    public function testUpdateAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->patchJson(
            '/api/terrafund/site/1',
            $this->siteData(),
            $headers,
        )
        ->assertStatus(200)
        ->assertJsonFragment(
            $this->siteData([
                'id' => 1,
            ])
        );
    }

    public function testUpdateActionRequiresAccess(): void
    {
        $token = Auth::attempt([
            'email_address' => 'joe@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->patchJson(
            '/api/terrafund/site/1',
            $this->siteData(),
            $headers,
        )
        ->assertStatus(403);
    }

    public function testReadAction(): void
    {
        $token = Auth::attempt([
            'email_address' => 'terrafund@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/site/1', $headers)
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => 1,
            'name' => 'Terrafund Site',
            'start_date' => '2020-01-01',
            'end_date' => '2021-01-01',
            'terrafund_programme_id' => 1,
        ])
        ->assertJsonPath('data.photos.0.id', 3);
    }

    public function testReadActionUserMustBeInNurseryProgramme(): void
    {
        $token = Auth::attempt([
            'email_address' => 'sue@example.com',
            'password' => 'Password123',
        ]);
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ];

        $this->getJson('/api/terrafund/nursery/1', $headers)
        ->assertStatus(403);
    }

    public function testReadMySitesAction(): void
    {
        $organisation = Organisation::factory()->create();
        $terrafundProgramme = TerrafundProgramme::factory()->create();
        $terrafundSite = TerrafundSite::factory()->create([
            'terrafund_programme_id' => $terrafundProgramme->id,
        ]);
        $missingSite = TerrafundSite::factory()->create();
        $user = User::factory()->create([
            'organisation_id' => $organisation->id,
        ]);
        $user->frameworks()->attach(2);
        $user->terrafundProgrammes()->attach($terrafundProgramme->id);

        $this->actingAs($user)
            ->getJson('/api/terrafund/my/sites')
            ->assertStatus(200)
            ->assertJsonFragment([
                'id' => $terrafundSite->id,
            ])
            ->assertJsonMissingExact([
                'id' => $missingSite->id,
            ]);
    }
}
