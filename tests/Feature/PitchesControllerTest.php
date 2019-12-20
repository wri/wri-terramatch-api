<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class PitchesControllerTest extends TestCase
{
    public function testCreateAction()
    {
        $data = [
            "name" => "Example Pitch 2",
            "description" => "Lorem ipsum dolor sit amet",
            "land_types" => ["bare_land", "wetland"],
            "land_ownerships" => ["reserve"],
            "land_size" => "lt_10",
            "land_continent" => "australia",
            "land_country" => "AU",
            "land_geojson" => "{\"type\":\"Polygon\",\"coordinates\":[[[-1.864006519317627,50.7219083651253],[-1.8627190589904783,50.7219083651253],[-1.8627190589904783,50.72276418262861],[-1.864006519317627,50.72276418262861],[-1.864006519317627,50.7219083651253]]]}",
            "restoration_methods" => ["assisted_natural", "ecological", "riparian_buffers"],
            "restoration_goals" => ["agriculture_and_commodities"],
            "funding_sources" => ["grant_with_limited_reporting"],
            "funding_amount" => 1234,
            "revenue_drivers" => ["carbon_credits"],
            "estimated_timespan" => 36,
            "long_term_engagement" => null,
            "reporting_frequency" => "bi_annually",
            "reporting_level" => "high",
            "sustainable_development_goals" => ["goal_5", "goal_7"],
            "avatar" => null,
            "cover_photo" => null,
            "video" => null,
            "problem" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
            "anticipated_outcome" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum',
            "who_is_involved" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum",
            "local_community_involvement" => true,
            "training_involved" => true,
            "training_type" => "remote",
            "training_amount_people" => 33,
            "people_working_in" => 'test string',
            "people_amount_nearby" => 10,
            "people_amount_abroad" => 3,
            "people_amount_employees" => 4,
            "people_amount_volunteers" => 4,
            "benefited_people" => 404,
            "future_maintenance" => "Lorem ipsum dolor sit amet, consec...",
            "use_of_resources" => "Lorem ipsum dolor sit amet, consec...",
            "facebook" => null,
            "twitter" => 'https://twitter.com/Twitter'
        ];
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->postJson("/api/pitches", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(201);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "status",
                "approved_rejected_by",
                "rejected_reason",
                "data" => [
                    "id",
                    "organisation_id",
                    "name",
                    "description",
                    "land_types",
                    "land_ownerships",
                    "land_size",
                    "land_continent",
                    "land_country",
                    "land_geojson",
                    "restoration_methods",
                    "restoration_goals",
                    "funding_sources",
                    "funding_amount",
                    "revenue_drivers",
                    "estimated_timespan",
                    "long_term_engagement",
                    "reporting_frequency",
                    "reporting_level",
                    "sustainable_development_goals",
                    "avatar",
                    "cover_photo",
                    "video",
                    'problem',
                    'anticipated_outcome',
                    'who_is_involved',
                    'local_community_involvement',
                    'training_involved',
                    'training_type',
                    'training_amount_people',
                    'people_working_in',
                    'people_amount_nearby',
                    'people_amount_abroad',
                    'people_amount_employees',
                    'people_amount_volunteers',
                    'benefited_people',
                    'future_maintenance',
                    'use_of_resources',
                    'facebook',
                    'twitter',
                    'instagram',
                    "completed",
                    "completed_at",
                    "completed_by",
                    "successful"
                ]
            ]
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
        $response = $this->getJson("/api/pitches/1", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "organisation_id",
                "name",
                "description",
                "land_types",
                "land_ownerships",
                "land_size",
                "land_continent",
                "land_country",
                "land_geojson",
                "restoration_methods",
                "restoration_goals",
                "funding_sources",
                "funding_amount",
                "revenue_drivers",
                "estimated_timespan",
                "long_term_engagement",
                "reporting_frequency",
                "reporting_level",
                "sustainable_development_goals",
                "avatar",
                "cover_photo",
                "video",
                'problem',
                'anticipated_outcome',
                'who_is_involved',
                'local_community_involvement',
                'training_involved',
                'training_type',
                'training_amount_people',
                'people_working_in',
                'people_amount_nearby',
                'people_amount_abroad',
                'people_amount_employees',
                'people_amount_volunteers',
                'benefited_people',
                'future_maintenance',
                'use_of_resources',
                'facebook',
                'twitter',
                'instagram',
                "completed",
                "completed_at",
                "completed_by",
                "successful"
            ]
        ]);
    }

    public function testUpdateAction()
    {
        $data = [
            "name" => "Another Example Pitch"
        ];
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $response = $this->patchJson("/api/pitches/1", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "status",
                "approved_rejected_by",
                "rejected_reason",
                "data" => [
                    "id",
                    "organisation_id",
                    "name",
                    "description",
                    "land_types",
                    "land_ownerships",
                    "land_size",
                    "land_continent",
                    "land_country",
                    "land_geojson",
                    "restoration_methods",
                    "restoration_goals",
                    "funding_sources",
                    "funding_amount",
                    "revenue_drivers",
                    "estimated_timespan",
                    "long_term_engagement",
                    "reporting_frequency",
                    "reporting_level",
                    "sustainable_development_goals",
                    "avatar",
                    "cover_photo",
                    "video",
                    'problem',
                    'anticipated_outcome',
                    'who_is_involved',
                    'local_community_involvement',
                    'training_involved',
                    'training_type',
                    'training_amount_people',
                    'people_working_in',
                    'people_amount_nearby',
                    'people_amount_abroad',
                    'people_amount_employees',
                    'people_amount_volunteers',
                    'benefited_people',
                    'future_maintenance',
                    'use_of_resources',
                    'facebook',
                    'twitter',
                    'instagram',
                    "completed",
                    "completed_at",
                    "completed_by",
                    "successful"
                ]
            ]
        ]);
    }

    public function testReadAllByOrganisationAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/organisations/1/pitches", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "organisation_id",
                    "name",
                    "description",
                    "land_types",
                    "land_ownerships",
                    "land_size",
                    "land_continent",
                    "land_country",
                    "land_geojson",
                    "restoration_methods",
                    "restoration_goals",
                    "funding_sources",
                    "funding_amount",
                    "revenue_drivers",
                    "estimated_timespan",
                    "long_term_engagement",
                    "reporting_frequency",
                    "reporting_level",
                    "sustainable_development_goals",
                    "avatar",
                    "cover_photo",
                    "video",
                    'problem',
                    'anticipated_outcome',
                    'who_is_involved',
                    'local_community_involvement',
                    'training_involved',
                    'training_type',
                    'training_amount_people',
                    'people_working_in',
                    'people_amount_nearby',
                    'people_amount_abroad',
                    'people_amount_employees',
                    'people_amount_volunteers',
                    'benefited_people',
                    'future_maintenance',
                    'use_of_resources',
                    'facebook',
                    'twitter',
                    'instagram',
                    "completed",
                    "completed_at",
                    "completed_by",
                    "successful"
                ]
            ]
        ]);
    }

    public function testSearchAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $data = [
        	"filters" => [[
                "attribute" =>  "land_types",
                "operator" =>  "contains",
                "value" => [
                    "mangrove",
                    "cropland"
                ]]],
            "sortDirection" => "desc",
            "page" =>  1
        ];
        $response = $this->postJson("/api/pitches/search", $data ,$headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "organisation_id",
                    "name",
                    "description",
                    "land_types",
                    "land_ownerships",
                    "land_size",
                    "land_continent",
                    "land_country",
                    "land_geojson",
                    "restoration_methods",
                    "restoration_goals",
                    "funding_sources",
                    "funding_amount",
                    "revenue_drivers",
                    "estimated_timespan",
                    "long_term_engagement",
                    "reporting_frequency",
                    "reporting_level",
                    "sustainable_development_goals",
                    "avatar",
                    "cover_photo",
                    "video",
                    'problem',
                    'anticipated_outcome',
                    'who_is_involved',
                    'local_community_involvement',
                    'training_involved',
                    'training_type',
                    'training_amount_people',
                    'people_working_in',
                    'people_amount_nearby',
                    'people_amount_abroad',
                    'people_amount_employees',
                    'people_amount_volunteers',
                    'benefited_people',
                    'future_maintenance',
                    'use_of_resources',
                    'facebook',
                    'twitter',
                    'instagram',
                    "compatibility_score",
                    "completed",
                    "completed_at",
                    "completed_by",
                    "successful"
                ]
            ]
        ]);
    }

    public function testInspectByOrganisationAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token
        ];
        $response = $this->getJson("/api/organisations/1/pitches/inspect", $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "status",
                    "approved_rejected_by",
                    "rejected_reason",
                    "data" => [
                        "id",
                        "organisation_id",
                        "name",
                        "description",
                        "land_types",
                        "land_ownerships",
                        "land_size",
                        "land_continent",
                        "land_country",
                        "land_geojson",
                        "restoration_methods",
                        "restoration_goals",
                        "funding_sources",
                        "funding_amount",
                        "revenue_drivers",
                        "estimated_timespan",
                        "long_term_engagement",
                        "reporting_frequency",
                        "reporting_level",
                        "sustainable_development_goals",
                        "avatar",
                        "cover_photo",
                        "video",
                        'problem',
                        'anticipated_outcome',
                        'who_is_involved',
                        'local_community_involvement',
                        'training_involved',
                        'training_type',
                        'training_amount_people',
                        'people_working_in',
                        'people_amount_nearby',
                        'people_amount_abroad',
                        'people_amount_employees',
                        'people_amount_volunteers',
                        'benefited_people',
                        'future_maintenance',
                        'use_of_resources',
                        'facebook',
                        'twitter',
                        'instagram',
                        "completed",
                        "completed_at",
                        "completed_by",
                        "successful"
                    ]
                ]
            ]
        ]);
    }

    public function testCompleteAction()
    {
        $token = Auth::attempt([
            "email_address" => "steve@example.com",
            "password" => "Password123"
        ]);
        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json"
        ];
        $data = [
            "successful" => true
        ];
        $response = $this->patchJson("/api/pitches/1/complete", $data, $headers);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "organisation_id",
                "name",
                "description",
                "land_types",
                "land_ownerships",
                "land_size",
                "land_continent",
                "land_country",
                "land_geojson",
                "restoration_methods",
                "restoration_goals",
                "funding_sources",
                "funding_amount",
                "revenue_drivers",
                "estimated_timespan",
                "long_term_engagement",
                "reporting_frequency",
                "reporting_level",
                "sustainable_development_goals",
                "avatar",
                "cover_photo",
                "video",
                'problem',
                'anticipated_outcome',
                'who_is_involved',
                'local_community_involvement',
                'training_involved',
                'training_type',
                'training_amount_people',
                'people_working_in',
                'people_amount_nearby',
                'people_amount_abroad',
                'people_amount_employees',
                'people_amount_volunteers',
                'benefited_people',
                'future_maintenance',
                'use_of_resources',
                'facebook',
                'twitter',
                'instagram',
                "completed",
                "completed_at",
                "completed_by",
                "successful"
            ]
        ]);
        $response->assertJson([
            "data" => [
                "completed" => true
            ]
        ]);
    }
}
