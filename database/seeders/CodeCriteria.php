<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CodeCriteria extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $criteria = [
            [
                'uuid' => $uuid1 = Str::uuid(),
                'uuid_primary' => $uuid1,
                'name' => 'Format GeoJSON',
                'description' => 'Flag: Output format is not GeoJSON',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => $uuid2 = Str::uuid(),
                'uuid_primary' => $uuid2,
                'name' => 'Projection WGS-84',
                'description' => 'Flag: CRS is not WGS-84 (EPSG 4326)',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => $uuid3 = Str::uuid(),
                'uuid_primary' => $uuid3,
                'name' => 'Overlapping Polygons',
                'description' => 'Flag: Overlapping polygons',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => $uuid4 = Str::uuid(),
                'uuid_primary' => $uuid4,
                'name' => 'Self-Intersection Flag',
                'description' => 'Flag: Self-intersecting polygon',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => $uuid5 = Str::uuid(),
                'uuid_primary' => $uuid5,
                'name' => 'Coordinate System Flag',
                'description' => 'Flag: Polygon bounding box (envelope) is outside (-/+ 180 , -/+ 90)',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => $uuid6 = Str::uuid(),
                'uuid_primary' => $uuid6,
                'name' => 'Size Limit Flag',
                'description' => 'Flag: Polygon area greater than 1000 ha',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => $uuid7 = Str::uuid(),
                'uuid_primary' => $uuid7,
                'name' => 'Within Country Flag',
                'description' => 'Flag: Polygon does not sit more than 25% outside of the expected country',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => $uuid8 = Str::uuid(),
                'uuid_primary' => $uuid8,
                'name' => 'Spike Flag',
                'description' => 'Flag: If the polygon boundary is composed of over 100 line segments and two adjoining boundary line segments contribute more than 25% of the total boundary path distance',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => $uuid9 = Str::uuid(),
                'uuid_primary' => $uuid9,
                'name' => 'Geometry Type Flag',
                'description' => 'Flag: The file’s geometry type is not one of: Polygon MultiPolygon 3D Polygon 3D MultiPolygon',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => $uuid10 = Str::uuid(),
                'uuid_primary' => $uuid10,
                'name' => 'Polygon Flag',
                'description' => 'Flag: A feature’s geometry type is not one of: Polygon MultiPolygon',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => $uuid11 = Str::uuid(),
                'uuid_primary' => $uuid11,
                'name' => '2-Dimension Flag',
                'description' => 'Flag: A feature’s geometry contains Z coordinates (3d points)',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => $uuid12 = Str::uuid(),
                'uuid_primary' => $uuid12,
                'name' => 'Total Area Expected Flag',
                'description' => 'Flag: Total polygon area (at site level) not between 75% and 125% of proposed restoration area',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => $uuid13 = Str::uuid(),
                'uuid_primary' => $uuid13,
                'name' => 'Table Schema Flag',
                'description' => 'Flag: Attribute table does not match schema',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => $uuid14 = Str::uuid(),
                'uuid_primary' => $uuid14,
                'name' => 'Data Completed Flag',
                'description' => 'Flag: Attribute table matches schema on a feature-by-feature level',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($criteria as $criterion) {
            DB::table('code_criteria')->insert($criterion);
        }
    }
}
