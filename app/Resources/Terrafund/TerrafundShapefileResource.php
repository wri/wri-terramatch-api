<?php

namespace App\Resources\Terrafund;

use App\Resources\Resource;

class TerrafundShapefileResource extends Resource
{
    public function __construct($shapefile)
    {
        $this->model = $shapefile->model;
        $this->model_id = $shapefile->model_id;
        $this->model_name = $shapefile->model_name;
        $this->boundary_geojson = $shapefile->boundary_geojson;
    }
}
