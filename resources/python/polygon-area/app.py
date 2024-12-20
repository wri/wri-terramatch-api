import sys
import json
import geopandas as gpd
from shapely.geometry import shape
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

def calculate_area(geometry):
    """
    Calculate area in hectares for a given geometry
    """
    try:
        gdf = gpd.GeoDataFrame(geometry=[geometry], crs="EPSG:4326")
        gdf_projected = gdf.to_crs('ESRI:54009')
        
        area_hectares = gdf_projected.geometry.area[0] / 10000
        
        return area_hectares
    except Exception as e:
        logger.error(f"Error calculating area: {str(e)}")
        raise

def main():
    try:
        if len(sys.argv) != 3:
            raise ValueError("Script requires input and output file paths as arguments")
            
        input_file = sys.argv[1]
        output_file = sys.argv[2]
        
        with open(input_file, 'r') as f:
            geojson_data = json.load(f)
        
        if 'type' in geojson_data and geojson_data['type'] == 'Feature':
            geometry = shape(geojson_data['geometry'])
        elif 'type' in geojson_data and geojson_data['type'] == 'FeatureCollection':
            geometry = shape(geojson_data['features'][0]['geometry'])
        else:
            geometry = shape(geojson_data)
            
        area = calculate_area(geometry)
        
        result = {
            'area_hectares': area,
            'original_geometry': geojson_data
        }
        
        with open(output_file, 'w') as f:
            json.dump(result, f)
            
        print(area)
        
    except Exception as e:
        logger.error(f"Error processing geometry: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    main()
