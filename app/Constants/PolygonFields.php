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
        'planting_status',
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
        'planting_status',
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
        'planting_status',
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
        'planting_status',
    ];
    /**
     * Basic polygon property fields for validation and export
     */
    public const VALIDATION_FIELDS = [
      'poly_name',
      'practice',
      'target_sys',
      'distr',
      'num_trees',
      'planting_status',
  ];
}
