<?php

namespace App\Validators\Extensions;

use Illuminate\Support\Facades\Config;

class TreeSpeciesOwner extends Extension
{
    public static $name = "tree_species_owner";
    public static $message = [
        "TREE_SPECIES_OWNER",
        "The {{attribute}} field must be a tree species owner.",
        ["attribute" => ":attribute"],
        "The :attribute field must be a tree species owner."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $treeSpeciesOwners = array_unique(array_values(Config::get("data.tree_species_owners")));
        return in_array($value, $treeSpeciesOwners);
    }
}
