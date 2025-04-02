import json
import sys
import os

import requests
import yaml
import gfw_api as gfw
import tree_cover_indicator as ttc
from fiona.model import Geometry, Feature, Properties


def generate_indicator(feature, indicator_name, params, session):
    if params["indicators"][indicator_name]["data_source"] == "gfw":
        supported_layers = gfw.get_supported_gfw_layer()
        if indicator_name not in supported_layers.keys():
            raise gfw.UnsupportedGFWLayer(indicator_name)
        polygon_gfw_data = gfw.get_gfw_data(
            feature.geometry, session, indicator_name, params
        )
        if params["indicators"][indicator_name]["area_source"] == "gfw":
            key_label = params["indicators"][indicator_name]["key_label"]
            key_value = params["indicators"][indicator_name]["key_value"]
            polygon_data = {
                row[key_label]: row[key_value] for row in polygon_gfw_data["data"]
            }
        else:
            key_label = params["indicators"][indicator_name]["key_label"]
            polygon_data = {
                row[key_label]: ttc.calculate_area(feature)
                for row in polygon_gfw_data["data"]
            }
    elif params["indicators"][indicator_name]["data_source"] == "polygon":
        polygon_data = {
            feature.properties[
                params["indicators"][indicator_name]["polygon_key"]
            ]: ttc.calculate_area(feature)
        }

    if params["indicators"][indicator_name]["zero_fill"]:
        values = {}
        for year in range(
            params["indicators"][indicator_name]["start_year"],
            params["indicators"][indicator_name]["end_year"] + 1,
        ):
            values[year] = polygon_data.get(year, 0.0)
        indicator_results = {indicator_name: values}
    else:
        indicator_results = {indicator_name: polygon_data}
    return indicator_results

def main():
    input_geojson = sys.argv[1]
    output_geojson = sys.argv[2]
    indicator_name = sys.argv[3]
    api_key = sys.argv[4]
    
    with open(input_geojson, "r") as f:
        geojson_data = json.load(f)

    config_path = "resources/python/polygon-indicator/config.yaml"

    absolute_config_path = os.path.abspath(config_path)
    print(f"Looking for config at: {absolute_config_path}")

    with open(config_path) as conf_file:
        config = yaml.safe_load(conf_file)

    with requests.Session() as session:
       session.headers = {
           "content-type": "application/json",
           "x-api-key": f"{api_key}",
       }
    
    fiona_feature = Feature(
        geometry=Geometry(
            type=geojson_data["geometry"]["type"],
            coordinates=geojson_data["geometry"]["coordinates"]
        ),
        properties=Properties(**geojson_data["properties"])
    )
    result = generate_indicator(fiona_feature, indicator_name, config, session)

    with open(output_geojson, 'w') as f:
        json.dump({'area': result}, f)

if __name__ == "__main__":
    main()
