import shapely
import pandas as pd
# from rasterstats import zonal_stats
import geopandas as gpd
import os
from shapely.geometry import Polygon, shape, box
from shapely.ops import transform
from exactextract import exact_extract
import rasterio
from pathlib import Path

import ttc_s3_utils as s3_utils
import ttc_error_utils as error


def get_gfw_data(geometry, session, dataset, params):
    url = f'{params["gfw_api"]["base_url"]}{params["indicators"]["wri_tropical_tree_cover"][dataset]["query_url"]}'
    sql = params["indicators"]["wri_tropical_tree_cover"][dataset]["sql"]
    payload = {"sql": sql, "geometry": shapely.geometry.mapping(geometry)}
    response = session.post(url, json=payload)
    response.raise_for_status()
    return response.json()


def calculate_area(feature):
    geometry = shape(feature["geometry"])
    gdf = gpd.GeoDataFrame(geometry=[geometry], crs="EPSG:4326")
    gdf = gdf.to_crs("EPSG:3857")
    area_m2 = gdf.geometry.area.values[
        0
    ]  # Directly get the area in square meters as a float
    area_ha = area_m2 / 10**4  # Convert to hectares
    return area_ha


def calculate_tree_cover(feature, project_name, params, logger):
    try:
        logger.debug("Calculating area...")
        area_ha = calculate_area(feature)
        logger.debug(f"Area calculated successfully: {area_ha}")
        mosaic_file = f"{str(params['gdal']['outfile'])}-{project_name}-{str(params['gdal']['outfile_suffix'])}.tif"
        temp_path = Path("temp.geojson")
        temp_path.write_text(shapely.to_geojson(shape(feature["geometry"])))
        with rasterio.open(mosaic_file) as mosaic:
            # Use exactextract to compute the mean
            result = exact_extract(
                mosaic,
                temp_path,
                "mean(min_coverage_frac=0.05, coverage_weight=fraction)",
            )
            expected_ttc = result[0]["properties"]["mean"]

        logger.debug(f"Expected tree cover calculated successfully: {expected_ttc}")

        logger.debug("Calculating shift error...")
        shift_error = error.calculate_shift_error(
            feature, expected_ttc, project_name, params, logger
        )
        logger.debug(f"Shift error calculated successfully: {shift_error}")

        logger.debug("Calculating LULC error...")

        lulc_lower_error, lulc_upper_error = error.calculate_lulc_error(
            feature, project_name, expected_ttc, params, logger
        )
        if lulc_lower_error == float("inf") or lulc_lower_error == float("-inf"):
            lulc_lower_error = 0
        if lulc_upper_error == float("inf") or lulc_upper_error == float("-inf"):
            lulc_upper_error = 0

        logger.debug(
            f"LULC error calculated successfully: {lulc_lower_error}, {lulc_upper_error}"
        )

        logger.debug("Calculating subregion error...")
        subregion, subregion_lower_error, subregion_upper_error = (
            error.calculate_subregion_error(feature, expected_ttc, params, logger)
        )

        logger.debug(
            f"Subregion error calculated successfully: {subregion}, {subregion_lower_error}, {subregion_upper_error}"
        )

        logger.debug("Calculating small site error...")
        small_site_error = error.get_small_site_error_value(
            area_ha, expected_ttc, params, logger
        )
        logger.debug(f"Small site error: {small_site_error}")
        logger.debug(f"Small site error calculated successfully: {small_site_error}")

        logger.debug("Integrating errors...")
        plus, minus, plus_minus_average = error.combine_errors(
            expected_ttc,
            shift_error,
            small_site_error,
            lulc_lower_error,
            lulc_upper_error,
            subregion_lower_error,
            subregion_upper_error,
        )

        tree_cover_result = {
            "TTC": expected_ttc,
            "error_plus": plus,
            "error_minus": minus,
            "plus_minus_average": plus_minus_average,
            "small_site_error": small_site_error,
            "lulc_lower_error": lulc_lower_error,
            "lulc_upper_error": lulc_upper_error,
            "shift_error": shift_error,
            "subregion_lower_error": subregion_lower_error,
            "subregion_upper_error": subregion_upper_error,
            'area_HA': area_ha
        }

        logger.debug(f"Tree cover result calculated successfully: {tree_cover_result}")
        return tree_cover_result
    except Exception as e:
        logger.error(f"Failed to calculate tree cover result: {e}", exc_info=True)
        return None


def process_features_by_project(project_gdf, project_name, logger, params):
    logger.info(f"Checking for TTC mosaic for {project_name}")
    mosaic_file = f"{str(params['gdal']['outfile'])}-{project_name}-{str(params['gdal']['outfile_suffix'])}.tif"
    if os.path.exists(mosaic_file):
        logger.debug("TTC mosaic file found")
    else:
        global_lookup = s3_utils.build_tile_lookup(params)
        logger.debug("Global tile lookup generated")
        tile_file_list = s3_utils.download_tiles(
            project_gdf, global_lookup, "project", params
        )
        logger.debug("Tiles downloaded")
        s3_utils.make_mosaic(tile_file_list, project_name, params)
        logger.debug("Tile mosaic generated")
    logger.debug(f"Mosaic file at: {mosaic_file}")
    error.prep_lulc_data(project_gdf, project_name, logger, params)
    poly_list = project_gdf["poly_name"].unique()
    logger.info(
        f"Calculating tre cover for {len(poly_list)} polygons in {project_name}"
    )
    project_poly_list = []
    poly_count = 0
    for poly in poly_list:
        poly_data = project_gdf[project_gdf["poly_name"] == poly]
        ttc_result = calculate_tree_cover(
            poly_data.iloc[0], project_name, params, logger
        )
        poly_count += 1
        logger.info(f"TTC result: {str(ttc_result)} for polygon {poly_count}")
        poly_name = poly_data["poly_name"]
        logger.debug(f"poly_name: {poly_name}")
        if ttc_result is None:
            ttc_result = {}
        ttc_result["poly_name"] = poly_data["poly_name"]
        ttc_result["Project"] = poly_data["Project"]
        project_poly_list.append(pd.DataFrame.from_dict(ttc_result))
    all_poly_df = pd.DataFrame(pd.concat(project_poly_list, ignore_index=True))
    data_path = str(params["base"]["data_path"])
    all_poly_df.to_csv(
        f"{data_path}ttc_output/ttc_from_tiles_{project_name}.csv", index=False
    )
    logger.info(f"Tree cover data calculated for {project_name}")
