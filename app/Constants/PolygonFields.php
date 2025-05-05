<?php

namespace App\Constants;

class PolygonFields
{
    /**
     * Basic polygon property fields for validation and export
     */
    public const BASIC_FIELDS = [
        'poly_name',
        'plantstart',
        'practice',
        'target_sys',
        'distr',
        'num_trees',
    ];

    /**
     * Extended polygon fields including identifiers
     */
    public const EXTENDED_FIELDS = [
        'poly_name',
        'plantstart',
        'practice',
        'target_sys',
        'distr',
        'num_trees',
        'site_id',
        'uuid',
    ];

    /**
     * Complete polygon fields including ID
     */
    public const COMPLETE_FIELDS = [
        'poly_name',
        'plantstart',
        'practice',
        'target_sys',
        'distr',
        'num_trees',
        'site_id',
        'uuid',
        'id',
    ];

    /**
     * Point properties list
     */
    public const POINT_PROPERTIES = [
        'site_id',
        'poly_name',
        'plantstart',
        'practice',
        'target_sys',
        'distr',
        'num_trees',
    ];
}
