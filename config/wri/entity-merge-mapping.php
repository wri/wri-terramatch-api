<?php

return [
    'models' => [
        'site' => [
            'frameworks' => [
                'terrafund' => [
                    'properties' => [
                        // Skip 'name' because the merged site keeps its name
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
                        'polygon_status' => 'long-text',
                        'technical_narrative' => 'long-text',
                        'shared_drive_link' => 'ensure-unique-string',
                    ],
                    'relations' => [
                        'disturbances' => 'move-to-merged',
                        'treeSpecies' => 'tree-species-merge',
                        'nonTreeSpecies' => 'tree-species-merge',
                    ],
                    'file-collections' => [
                        'photos' => 'move-to-merged',
                    ],
                    'conditionals' => [
                        'site-rep-rel-disturbances',
                        'site-rep-technical-narrative',
                    ]
                ]
            ]
        ]
    ]
];
