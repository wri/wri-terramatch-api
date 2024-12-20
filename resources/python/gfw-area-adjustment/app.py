import sys
import json
import geopandas as gpd
from shapely.geometry import shape, Polygon
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

def calculate_correction_factor(reference_geometry):
    """
    Calculate the correction factor by comparing WGS84 and ESRI:54009 areas
    All areas are converted to hectares (1 hectare = 10000 square meters)
    """
    try:
        gdf_wgs84 = gpd.GeoDataFrame(geometry=[reference_geometry], crs="EPSG:4326")
        
        gdf_projected = gdf_wgs84.to_crs('ESRI:54009')
        area_projected_ha = gdf_projected.area[0] / 10000
        
        if area_projected_ha < 0.0001:
            logger.warning("Area is very small, using correction factor of 1")
            return 1.0
            
        area_geodesic_ha = gdf_wgs84.geometry.to_crs('+proj=cea').area[0] / 10000
        
        correction_factor = area_projected_ha / area_geodesic_ha if area_geodesic_ha > 0 else 1
        
        if correction_factor > 10 or correction_factor < 0.1:
            logger.warning(f"Extreme correction factor detected: {correction_factor}. Using 1.0 instead.")
            return 1.0
            
        return correction_factor
        
    except Exception as e:
        logger.error(f"Error calculating correction factor: {str(e)}")
        raise

def adjust_gfw_data(gfw_data, reference_geometry):
    """
    Adjust GFW area values using the correction factor from reference geometry
    """
    try:
        if isinstance(reference_geometry, str):
            reference_geometry = json.loads(reference_geometry)
            
        if 'type' in reference_geometry and reference_geometry['type'] == 'Feature':
            geometry = shape(reference_geometry['geometry'])
        elif 'type' in reference_geometry and reference_geometry['type'] == 'FeatureCollection':
            geometry = shape(reference_geometry['features'][0]['geometry'])
        else:
            geometry = shape(reference_geometry)
        
        correction_factor = calculate_correction_factor(geometry)
        
        if isinstance(gfw_data, str):
            gfw_data = json.loads(gfw_data)
        
        adjusted_data = {
            "data": [],
            "status": gfw_data.get("status", "success")
        }
        
        for entry in gfw_data.get("data", []):
            adjusted_entry = entry.copy()
            if entry["area__ha"] > 0.0001:
                adjusted_entry["area__ha"] = round(entry["area__ha"] * correction_factor, 5)
            adjusted_data["data"].append(adjusted_entry)
        
        return adjusted_data
        
    except Exception as e:
        logger.error(f"Error adjusting GFW data: {str(e)}")
        raise

def main():
    try:
        if len(sys.argv) != 4:
            raise ValueError("Script requires GFW data, reference geometry, and output file paths as arguments")
            
        gfw_data_file = sys.argv[1]
        reference_geometry_file = sys.argv[2]
        output_file = sys.argv[3]
        
        with open(gfw_data_file, 'r') as f:
            gfw_data = json.load(f)
        
        with open(reference_geometry_file, 'r') as f:
            reference_geometry = json.load(f)
            
        result = adjust_gfw_data(gfw_data, reference_geometry)
        
        with open(output_file, 'w') as f:
            json.dump(result, f)
            
    except Exception as e:
        logger.error(f"Error processing data: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    main()