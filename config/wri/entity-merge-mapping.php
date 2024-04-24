<?php

return [
    'models' => [
        'site' => [
            'frameworks' => [
                'terrafund' => [
                    'properties' => [
                        // Skip 'name' because it's from the merged site
                        'start_date' => 'date:first',
                        'end_date' => 'date:last',
                        'landscape_community_contribution' => 'long-text',
                        'boundary_geojson' => 'set-null',
                        'land_use_types' => 'union',
                        'restoration_strategy' => 'union',
                        'hectares_to_restore_goal' => 'sum',
                        'land_tenures' => 'union',
                    ],
                    'relations' => [
                        'disturbances' => 'move-to-merged',
                    ],
                    'file-collections' => [
                        'photos' => 'move-to-merged',
                    ],
                ]
            ]
        ],
        'site-report' => [
            'frameworks' => [
                'terrafund' => [
                    'properties' => [

                    ],
                    'linked-fields' => [

                    ],
                    'conditionals' => [

                    ]
                ]
            ]
        ]
    ]
];
