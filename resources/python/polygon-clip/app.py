import json
import sys
from pathlib import Path
from itertools import combinations

import pyproj
from shapely.geometry import shape, mapping
from shapely.ops import transform

# Constants
WGS84_CRS = pyproj.crs.CRS("epsg:4326")

def m2_to_hectare(meter_square):
    return float(meter_square / 10000)

def shape_hectares_from_wgs84(geom):
    center = geom.envelope.centroid
    proj_str = f"+ellps=WGS84 +proj=tmerc +lon_0={center.x} +lat_0={center.y} +k=1 +x_0=0 +y_0=0"
    crs_dst = pyproj.crs.CRS(proj_str)
    transformer = pyproj.Transformer.from_crs(WGS84_CRS, crs_dst)
    geom_reproj = transform(transformer.transform, geom)
    return m2_to_hectare(geom_reproj.area)

def process_features(features):
    try:
        orig_data = []
        for i, f in enumerate(features):
            orig_data.append((i, {
                'geometry': shape(f['geometry']),
                'properties': dict(f['properties'])
            }))
        return orig_data
    except Exception as e:
        print(f"Error in process_features: {e}")

def fix_overlaps(orig_data):
    try:
        changes = {}
        for (a, b) in combinations(orig_data, 2):
            a_idx, a_feat = a
            b_idx, b_feat = b
            a_geom = a_feat['geometry']
            b_geom = b_feat['geometry']
            if a_idx in changes:
                a_geom = changes[a_idx]
            if b_idx in changes:
                b_geom = changes[b_idx]
            if a_geom.intersects(b_geom):
                smaller, larger = sorted([(a_idx, a_geom), (b_idx, b_geom)], key=lambda x: x[1].area)
                smaller_idx, smaller_geom = smaller
                larger_idx, larger_geom = larger
                try:
                    overlap_shape = smaller_geom.intersection(larger_geom)
                    pct_overlap = 100 * overlap_shape.area / smaller_geom.area
                    area_overlap = shape_hectares_from_wgs84(overlap_shape)
                    if (pct_overlap <= 3.5) and (area_overlap <= 0.1):
                        larger_geom_new = larger_geom.difference(smaller_geom)
                        changes[larger_idx] = larger_geom_new
                except Exception as e:
                    print(f"Error processing overlap: {e}")
                    continue
        print(f"Fixed {len(changes)} overlaps")
        return changes
    except Exception as e:
        print(f"Error in fix_overlaps: {e}")

def create_output_geojson(orig_data, changes):
    try:
        output_features = []
        for i, feat in orig_data:
            if i in changes:
                feat['geometry'] = changes[i]
            output_features.append({
                "type": "Feature",
                "geometry": mapping(feat['geometry']),
                "properties": feat['properties']
            })
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

        orig_data = process_features(features)
        changes = fix_overlaps(orig_data)
        output_geojson = create_output_geojson(orig_data, changes)

        with open(output_geojson_path, "w") as output_file:
            json.dump(output_geojson, output_file)
            print(f"Output GeoJSON written to: {output_geojson_path}")

    except Exception as e:
        print(f"Error in main: {e}")

if __name__ == "__main__":
    import argparse

    parser = argparse.ArgumentParser(
        description="Fix small amounts of overlap by cutting into larger polygons."
    )
    parser.add_argument("input_geojson", type=str, help="Path to the input GeoJSON file")
    parser.add_argument("output_geojson", type=str, help="Path to the output GeoJSON file")
    args = parser.parse_args()

    main(args.input_geojson, args.output_geojson)