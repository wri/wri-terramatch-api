import json
import os
from math import pi

import pyproj
from shapely import voronoi_polygons
from shapely.geometry import GeometryCollection, MultiPoint, Point, mapping, shape
from shapely.ops import transform

# Constants
WGS84_CRS = pyproj.crs.CRS("epsg:4326")
BUFFER_ENVELOPE_SIZE = 5000
ADDITIONAL_RADIUS = 5


def calculate_circle_radius(hectares_area, additional_radius=ADDITIONAL_RADIUS):
    try:
        square_meters = hectares_area * 10000  # transform hectares into square meters
        radius = (square_meters / pi) ** 0.5  # calculate the radius of the circle
        return radius + additional_radius
    except Exception as e:
        print(f"Error in calculate_circle_radius: {e}")


def process_features(features):
    try:
        multipoint_list = []
        for f in features:
            geometry = f.get("geometry")
            if geometry:
                multipoint_list.append(shape(geometry))

        multipoint = MultiPoint(multipoint_list)
        print("Calculating the centroid of the multipoint")
        center = multipoint.centroid
        proj_str = f"+ellps=WGS84 +proj=tmerc +lat_0={center.y} +lon_0={center.x} +units=m +no_defs"
        crs_dst = pyproj.crs.CRS(proj_str)
        to_tmp_tmerc = pyproj.Transformer.from_crs(WGS84_CRS, crs_dst).transform

        transformed_points = []
        buffered_points = []
        for feature in features:
            point = shape(feature.get("geometry", {"type": "Point", "coordinates": [0, 0]}))
            properties = feature.get("properties", {})
            est_area = properties.get("est_area", 0)
            buffer_distance = calculate_circle_radius(est_area)
            transformed_point = transform(to_tmp_tmerc, Point(point.x, point.y))
            transformed_points.append(transformed_point)
            buffered_points.append(transformed_point.buffer(buffer_distance))

        return multipoint, transformed_points, buffered_points, crs_dst
    except Exception as e:
        print(f"Error in process_features: {e}")


def generate_voronoi_polygons(transformed_points):
    try:
        envelope = GeometryCollection(transformed_points).envelope.buffer(BUFFER_ENVELOPE_SIZE)
        return voronoi_polygons(MultiPoint(transformed_points), extend_to=envelope)
    except Exception as e:
        print(f"Error in generate_voronoi_polygons: {e}")


def create_output_geojson(features, transformed_points, buffered_points, voronoi_regions, crs_dst):
    try:
        to_wgs84 = pyproj.Transformer.from_crs(crs_dst, WGS84_CRS).transform

        voronoi_polygons_per_point = [0] * len(transformed_points)
        for i, point in enumerate(transformed_points):
            for region in voronoi_regions.geoms:
                if point.intersects(region):
                    voronoi_polygons_per_point[i] = region

        output_features = []
        for i, (voronoi_polygon, buffered_point, feature) in enumerate(
            zip(voronoi_polygons_per_point, buffered_points, features)
        ):
            intersection_region = buffered_point.intersection(voronoi_polygon)
            if intersection_region.is_valid and not intersection_region.is_empty:
                region_in_wgs84 = transform(to_wgs84, intersection_region)
                properties = feature.get("properties", {})
                output_features.append(
                    {
                        "type": "Feature",
                        "geometry": mapping(region_in_wgs84),
                        "properties": properties,
                    }
                )

        return {"type": "FeatureCollection", "features": output_features}
    except Exception as e:
        print(f"Error in create_output_geojson: {e}")


def main(input_geojson_path, output_geojson_path):
    try:
        with open(input_geojson_path, "r") as file:
            print("Reading GeoJSON file")
            geojson_data = json.load(file)

        print("GeoJSON file read successfully")
        features = geojson_data.get("features", [])
        if not features:
            print("No features found in the GeoJSON file")
            return

        multipoint, transformed_points, buffered_points, crs_dst = process_features(features)
        voronoi_regions = generate_voronoi_polygons(transformed_points)
        output_geojson = create_output_geojson(
            features, transformed_points, buffered_points, voronoi_regions, crs_dst
        )

        with open(output_geojson_path, "w") as output_file:
            json.dump(output_geojson, output_file)
            print(f"Output GeoJSON written to: {output_geojson_path}")

    except Exception as e:
        print(f"Error in main: {e}")


if __name__ == "__main__":
    import argparse

    parser = argparse.ArgumentParser(
        description="Process a GeoJSON file and generate a Voronoi diagram."
    )
    parser.add_argument("input_geojson", type=str, help="Path to the input GeoJSON file")
    parser.add_argument("output_geojson", type=str, help="Path to the output GeoJSON file")
    args = parser.parse_args()

    main(args.input_geojson, args.output_geojson)
