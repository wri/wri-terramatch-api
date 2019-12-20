<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class OrganisationsControllerTest extends TestCase
{
    public function testCreateAction()
    {
        $data = [
            "name" => "Acme Corporation",
            "description" => "Lorem ipsum dolor sit amet",
            "address_1" => "1 Foo Road",
            "address_2" => null,
            "city" => "Bar Town",
            "state" => "Baz State",
            "zip_code" => "Qux",
            "country" => "GB",
            "phone_number" => "0123456789",
            "website" => "http://www.example.com",
            "type" => "other",
            "category" => "both",
            "facebook" => null,
            "twitter" => null,
            "linkedin" => null,
            "instagram" => null,
            "avatar" => null,
            "cover_photo" => null,
            "video" => null,
            "founded_at" => "2000-01-01"
        ];
        $token = Auth::attempt([
            "email_address" => "joe@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/organisations", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "rejected_reason",
                "approved_rejected_by",
                "approved_rejected_at",
                "status",
                "data" => [
                    "id",
                    "name",
                    "description",
                    "address_1",
                    "address_2",
                    "city",
                    "state",
                    "zip_code",
                    "country",
                    "phone_number",
                    "website",
                    "type",
                    "category",
                    "facebook",
                    "twitter",
                    "linkedin",
                    "instagram",
                    "cover_photo",
                    "avatar",
                    "video",
                    "founded_at"
                ]
            ],
            "meta" => []
        ]);
    }

    public function testReadAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/organisations/1", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "name",
                "description",
                "address_1",
                "address_2",
                "city",
                "state",
                "zip_code",
                "country",
                "phone_number",
                "website",
                "type",
                "category",
                "facebook",
                "twitter",
                "linkedin",
                "instagram",
                "cover_photo",
                "avatar",
                "video",
                "founded_at"
            ]
        ]);
    }

    public function testUpdateAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $data = [
            "name" => "Acme Corporation 3",
            "phone_number" => "+44123456789",
            "website" => "https://www.example.com"
        ];
        $response = $this->patchJson("/api/organisations/1", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "status",
                "rejected_reason",
                "approved_rejected_by",
                "data" => [
                    "id",
                    "name",
                    "description",
                    "address_1",
                    "address_2",
                    "city",
                    "state",
                    "zip_code",
                    "country",
                    "phone_number",
                    "website",
                    "type",
                    "category",
                    "facebook",
                    "twitter",
                    "linkedin",
                    "instagram",
                    "cover_photo",
                    "avatar",
                    "video",
                    "founded_at"
                ]
            ]
        ]);
    }

    public function testReadAllAction()
    {
        $token = Auth::attempt([
            "email_address" => "jane@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/organisations", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "name",
                    "description",
                    "address_1",
                    "address_2",
                    "city",
                    "state",
                    "zip_code",
                    "country",
                    "phone_number",
                    "website",
                    "type",
                    "category",
                    "facebook",
                    "twitter",
                    "linkedin",
                    "instagram",
                    "cover_photo",
                    "avatar",
                    "video",
                    "founded_at"
                ]
            ]
        ]);
    }
}
