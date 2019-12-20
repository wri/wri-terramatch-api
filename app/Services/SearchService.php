<?php

namespace App\Services;

use App\Exceptions\InvalidSearchConditionsException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * This class contains the logic for parsing request parameters, validating
 * them, and generating a conditions object. The conditions object can then
 * be passed to a scope (on a model) to apply all the where, orderBy, offset,
 * and limit calls.
 */
class SearchService
{
    public const SORT_ATTRIBUTES = [
        "created_at",
        "funding_amount",
        "compatibility_score"
    ];
    public const SORT_DIRECTIONS = [
        "asc",
        "desc"
    ];
    public const PER_PAGE = 10;
    public const FILTER_ATTRIBUTES_OPERATORS = [
        "land_types" => [
            "contains"
        ],
        "land_ownerships" => [
            "contains"
        ],
        "land_size" => [
            "in"
        ],
        "land_continent" => [
            "in"
        ],
        "land_country" => [
            "in"
        ],
        "restoration_methods" => [
            "contains"
        ],
        "restoration_goals" => [
            "contains"
        ],
        "funding_sources" => [
            "contains"
        ],
        "funding_amount" => [
            "between"
        ],
        "long_term_engagement" => [
            "boolean"
        ],
        "reporting_frequency" => [
            "in"
        ],
        "reporting_level" => [
            "in"
        ],
        "sustainable_development_goals" => [
            "contains"
        ],
        "price_per_tree" => [
            "between"
        ]
    ];

    public function parse(Request $request): Conditions
    {
        $conditions = new Conditions();
        $conditions = $this->parsePage($request, $conditions);
        $conditions = $this->parseSort($request, $conditions);
        $conditions = $this->parseFilters($request, $conditions);
        return $conditions;
    }

    private function parsePage(Request $request, Conditions $conditions): Conditions
    {
        $page = $request->json()->get("page", 1);
        if (!is_int($page)) {
            throw new InvalidSearchConditionsException();
        }
        if ($page < 1) {
            throw new InvalidSearchConditionsException();
        }
        $conditions->page = $page;
        $conditions->offset = SearchService::PER_PAGE * ($page - 1);
        $conditions->limit = SearchService::PER_PAGE;
        return $conditions;
    }

    private function parseSort(Request $request, Conditions $conditions): Conditions
    {
        $sortAttribute = $request->json()->get("sortAttribute", "created_at");
        if (!in_array($sortAttribute, SearchService::SORT_ATTRIBUTES)) {
            throw new InvalidSearchConditionsException();
        }
        $conditions->orderColumn = $sortAttribute;
        $sortDirection = $request->json()->get("sortDirection", "asc");
        if (!in_array($sortDirection, SearchService::SORT_DIRECTIONS)) {
            throw new InvalidSearchConditionsException();
        }
        $conditions->orderDirection = $sortDirection;
        return $conditions;
    }

    private function parseFilters(Request $request, Conditions $conditions): Conditions
    {
        $filters = $request->json()->get("filters", []);
        if (!is_array($filters)) {
            throw new InvalidSearchConditionsException();
        }
        foreach ($filters as $filter) {
            if (!is_array($filter) || !$this->isValidFilter($filter)) {
                throw new InvalidSearchConditionsException();
            }
            $conditions->where[] = [
                $filter["attribute"],
                $filter["operator"],
                $filter["value"]
            ];
        }
        return $conditions;
    }

    private function isValidFilter(array $filter): bool
    {
        if (count($filter) != 3) {
            return false;
        }
        if (!array_key_exists("attribute", $filter) || !is_string($filter["attribute"])) {
            return false;
        }
        $attributes = array_keys(SearchService::FILTER_ATTRIBUTES_OPERATORS);
        if (!in_array($filter["attribute"], $attributes)) {
            return false;
        }
        if (!array_key_exists("operator", $filter) || !is_string($filter["operator"])) {
            return false;
        }
        $operators = SearchService::FILTER_ATTRIBUTES_OPERATORS[$filter["attribute"]];
        if (!in_array($filter["operator"], $operators)) {
            return false;
        }
        if (!array_key_exists("value", $filter)) {
            return false;
        }
        if ($filter["operator"] == "contains" || $filter["operator"] == "in") {
            if (!is_array($filter["value"])) {
                return false;
            }
            foreach ($filter["value"] as $value) {
                if (!is_string($value)) {
                    return false;
                }
            }
        } else if ($filter["operator"] == "between") {
            if (!is_array($filter["value"]) || count($filter["value"]) != 2) {
                return false;
            }
            foreach ($filter["value"] as $value) {
                if (!is_int($value) && !is_float($value)) {
                    return false;
                }
            }
        } else if ($filter["operator"] == "boolean") {
            $booleans = ["true", "false"];
            if (!is_string($filter["value"]) || !in_array($filter["value"], $booleans)) {
                return false;
            }
        }
        return true;
    }

    public function summarise(String $model, Array $resources, Conditions $conditions): Object
    {
        switch ($model) {
            case "Offer":
                $total = DB::select("SELECT count(`id`) AS 'total' FROM `offers`;")[0]->total;
                break;
            case "Pitch":
                $total = DB::select("SELECT count(`id`) AS 'total' FROM `pitch_versions` WHERE `status` = 'approved';")[0]->total;
                break;
            default:
                throw new Exception();
        }
        return (object) [
            "count" => count($resources),
            "first" => 1,
            "current" => $conditions->page,
            "last" => ceil($total / SearchService::PER_PAGE),
            "total" => $total
        ];
    }
}