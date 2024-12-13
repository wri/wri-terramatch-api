import os
import shapely
import fiona
import rasterio
from rasterstats import zonal_stats
import pandas as pd
from osgeo import gdal
from shapely.geometry import Polygon, shape, box

# from shapely import concave_hull
from shapely.ops import transform, unary_union


# general
def calculate_percent_error(obs, exp):
    if exp != 0:
        return (obs - exp) / exp
    else:
        return 0


def force_2d(geometry):
    return transform(lambda x, y, z=None: (x, y), geometry)


def combine_errors(
    expected_ttc,
    shift_error,
    small_site_error,
    lulc_lower_error,
    lulc_upper_error,
    subregion_lower_error,
    subregion_upper_error,
):
    shift_error_half = shift_error / 2
    small_site_error_half = small_site_error / 2
    lower_error = (
        lulc_lower_error**2
        + subregion_lower_error**2
        + shift_error_half**2
        + small_site_error_half**2
    ) ** 0.5
    upper_error = (
        lulc_upper_error**2
        + subregion_upper_error**2
        + shift_error_half**2
        + small_site_error_half**2
    ) ** 0.5
    minus = expected_ttc * lower_error
    plus = expected_ttc * upper_error
    plus_minus_average = (minus + plus) / 2
    return plus, minus, plus_minus_average


# shift error
def shift_geometry(feature, NS, EW, params):
    offset = params["indicators"]["wri_tropical_tree_cover"]["shift_value"]
    # Convert feature to 2D
    geometry = shape(feature["geometry"])
    geometry_2d = force_2d(geometry)

    if geometry_2d.geom_type == "Polygon":
        coords = list(geometry_2d.exterior.coords)
    elif geometry_2d.geom_type == "MultiPolygon":
        coords = list(
            (
                unary_union(
                    (
                        gpd.GeoDataFrame(
                            {"geometry": geometry_2d, "test": [1]}
                        ).explode(ignore_index=True)
                    )["geometry"]
                )
            ).geometry.exterior.coords
        )

    # Shift coordinates
    new_coords = []
    for i, (lat, lon) in enumerate(coords):
        if NS == "N":
            lat = lat + offset
        if NS == "S":
            lat = lat - offset
        if EW == "E":
            lon = lon + offset
        if EW == "W":
            lon = lon - offset
        new_coords.append((lat, lon))
    new_polygon = Polygon(new_coords)
    return new_polygon


def calculate_shift_error(feature, expected_ttc, project_name, params, logger):
    shift_list = [
        ["N", 0],
        ["S", 0],
        [0, "E"],
        [0, "W"],
        ["N", "E"],
        ["N", "W"],
        ["S", "E"],
        ["S", "W"],
    ]
    shift_ttc = []
    mosaic_file = f"{str(params['gdal']['outfile'])}-{project_name}-{str(params['gdal']['outfile_suffix'])}.tif"
    for i in shift_list:
        shift_feature = shift_geometry(feature, i[0], i[1], params)
        logger.debug(f"shift calculated for {str(i)}")
        if params["indicators"]["wri_tropical_tree_cover"]["data_source"] == "tiles":
            with rasterio.open(mosaic_file) as src:
                affine = src.transform
                array = src.read(1)
            shift_data = zonal_stats(
                shift_feature, array, affine=affine, stats="mean", all_touched=True
            )[0]["mean"]
            logger.debug(f"Shift TTC: {shift_data}")
            shift_ttc.append(shift_data)
    shift_error = [calculate_percent_error(i, expected_ttc) for i in shift_ttc]
    sq_shift_error = [i**2 for i in shift_error]
    all_shift_error = (sum(sq_shift_error) / 8) ** 0.5
    return all_shift_error


# LULC error
def find_lulc_label(lulc_int, conversion_dict):
    for key, value in conversion_dict.items():
        if lulc_int in value:
            return key
    return None


def find_lulc_error_data(lulc_label, lulc_error_table):
    lulc_error_table["category"] = lulc_error_table["category"].str.lower()
    # print("Lulc error table category:", lulc_error_table["category"])
    return lulc_error_table[lulc_error_table["category"] == lulc_label]


def prep_lulc_data(features_gdf, project_name, logger, params):
    target_crs = params["indicators"]["wri_tropical_tree_cover"]["lulc"]["target_crs"]
    initial_crs = features_gdf.crs.srs
    if initial_crs is None:
        initial_crs = params["indicators"]["wri_tropical_tree_cover"]["lulc"][
            "default_initial_crs"
        ]
        features_gdf.set_crs(initial_crs, inplace=True)
    reproj = features_gdf.to_crs(
        crs=params["indicators"]["wri_tropical_tree_cover"]["lulc"]["reproj_crs"]
    )
    buffer = reproj.buffer(
        params["indicators"]["wri_tropical_tree_cover"]["lulc"]["buffer_size"],
        cap_style=3,
    )
    buffer = buffer.to_crs(crs=target_crs)
    xmin, ymin, xmax, ymax = buffer.total_bounds
    logger.debug(f"xmin: {xmin}, ymin: {ymin}, xmax: {xmax}, ymax: {ymax}")

    data_path = params["base"]["data_path"]
    temp_path = params["base"]["temp_path"]
    lulc_global_name = params["indicators"]["wri_tropical_tree_cover"]["lulc"][
        "input_path"
    ]
    lulc_prefix = params["indicators"]["wri_tropical_tree_cover"]["lulc"][
        "temp_output_prefix"
    ]

    global_lulc_file = f"{data_path}{lulc_global_name}"

    ds = gdal.Open(global_lulc_file)
    output_file = f"{temp_path}{lulc_prefix}{project_name}.tif"

    translateoptions = gdal.TranslateOptions(projWin=[xmin, ymax, xmax, ymin])
    ds = gdal.Translate(output_file, ds, options=translateoptions)
    logger.debug(f"Temp LULC file for {project_name} generated at {output_file}")

    warpoptions = gdal.WarpOptions(warpOptions=["CENTER_LONG 0"])
    ds = gdal.Warp(output_file, output_file, options=warpoptions)


def get_lulc_by_polygon(feature, project_name, logger, params):
    geometry = shape(feature["geometry"])
    temp_path = params["base"]["temp_path"]
    lulc_prefix = params["indicators"]["wri_tropical_tree_cover"]["lulc"][
        "temp_output_prefix"
    ]
    input_file = f"{temp_path}{lulc_prefix}{project_name}.tif"
    if os.path.exists(input_file):
        lulc = zonal_stats(
            geometry,
            input_file,
            all_touched=True,
            stats=params["indicators"]["wri_tropical_tree_cover"]["lulc"][
                "zonal_stats"
            ],
            nodata=255,
        )
        logger.debug(
            f"Zonal stats count: {lulc[0]['count']}, zonal stats majority: {lulc[0]['majority']}"
        )
        return lulc[0]["count"], lulc[0]["majority"]
    else:
        raise FileNotFoundError(errno.ENOENT, os.strerror(errno.ENOENT), input_file)


def calculate_lulc_error(feature, project_name, expected_ttc, params, logger):
    logger.debug(f"Calculating LULC data for {project_name}")
    logger.debug(f"expected_ttc: {expected_ttc}")
    if expected_ttc == 0:
        lulc_lower_error = 0
        lulc_upper_error = 0
        return float(lulc_lower_error), float(lulc_upper_error)
    else:
        lulc_count, lulc_majority = get_lulc_by_polygon(
            feature, project_name, logger, params
        )
        if lulc_count > 0:

            logger.debug("getting lulc error table")
            lulc_error_table = pd.read_csv(
                params["indicators"]["wri_tropical_tree_cover"]["lulc_ci_data"]["path"]
            )
            lulc_conversion_dict = params["indicators"]["wri_tropical_tree_cover"][
                "esa_lulc_conversions"
            ]
            lulc_int = int(lulc_majority)
            logger.debug(f"lulc_int: {lulc_int}")
            lulc_label = find_lulc_label(lulc_int, lulc_conversion_dict)
            logger.debug(f"lulc_label: {lulc_label}")
            lulc_error_table = find_lulc_error_data(lulc_label, lulc_error_table)

            upper_error = (
                lulc_error_table["r_upper_95"] - lulc_error_table["p_lower_95"]
            )
            lower_error = (
                lulc_error_table["p_upper_95"] - lulc_error_table["r_lower_95"]
            )
            logger.debug(f"lulc_upper_error: {upper_error}")
            logger.debug(f"lulc_lower_error: {lower_error}")

            observed_lower_lulc = expected_ttc + lower_error
            observed_upper_lulc = expected_ttc + upper_error
            lulc_lower_error = (observed_lower_lulc - expected_ttc) / expected_ttc
            lulc_upper_error = (observed_upper_lulc - expected_ttc) / expected_ttc

            logger.debug(f"lulc_lower_error: {lulc_lower_error}")
            logger.debug(f"lulc_upper_error: {lulc_upper_error}")

            return float(lulc_lower_error), float(lulc_upper_error)
        else:
            logger.error("Missing LULC data")


# subregion error
def calculate_subregion_error(feature, expected_ttc, params, logger):
    with fiona.open(
        params["indicators"]["wri_tropical_tree_cover"]["subregion_ci_data"]["path"],
        "r",
    ) as shpin:
        subregion_features = list(shpin)
    subregion_polys = [shape(poly["geometry"]) for poly in subregion_features]
    centroid = shape(feature["geometry"]).centroid
    intersect_list = [
        feat
        for feat, poly in zip(subregion_features, subregion_polys)
        if poly.intersects(centroid)
    ]
    logger.debug(f"Intersection list length: {len(intersect_list)}")

    if intersect_list:
        intersect_feature = intersect_list[0]
        category = intersect_feature["properties"]["category"]
        p_lower_95 = intersect_feature["properties"]["p_lower_95"]
        r_lower_95 = intersect_feature["properties"]["r_lower_95"]
        p_upper_95 = intersect_feature["properties"]["p_upper_95"]
        r_upper_95 = intersect_feature["properties"]["r_upper_95"]
        upper_error = r_upper_95 - p_lower_95
        lower_error = p_upper_95 - r_lower_95
        observed_lower_subregion = expected_ttc + lower_error
        observed_upper_subregion = expected_ttc + upper_error
        subregion_lower_error = calculate_percent_error(
            observed_lower_subregion, expected_ttc
        )
        subregion_upper_error = calculate_percent_error(
            observed_upper_subregion, expected_ttc
        )
        return category, subregion_lower_error, subregion_upper_error
    else:
        logger.debug("No subregion intersection found")
        return None, 0, 0


# small site error
def get_small_site_error_value(area, expected_ttc, params, logger):
    small_sites_error = params["indicators"]["wri_tropical_tree_cover"][
        "small_sites_error"
    ]
    if (
        area
        <= params["indicators"]["wri_tropical_tree_cover"]["small_sites_area_thresh"]
    ):
        logger.debug(
            f'Polygon area of {area}ha is below threshold of {params["indicators"]["wri_tropical_tree_cover"]["small_sites_area_thresh"]}'
        )
        logger.debug(f"Expected TTC: {expected_ttc}")
        if (
            float(small_sites_error["zeroToNine"]["min"])
            <= expected_ttc
            <= float(small_sites_error["zeroToNine"]["max"])
        ):
            logger.debug(f"Small sites error is 0 - 9")
            return float(small_sites_error["zeroToNine"]["error"]) / expected_ttc

        elif (
            float(small_sites_error["tenToThirtyNine"]["min"])
            < expected_ttc
            <= float(small_sites_error["tenToThirtyNine"]["max"])
        ):
            logger.debug(f"Small sites error is 10 - 39")
            return float(small_sites_error["tenToThirtyNine"]["error"]) / expected_ttc
        elif (
            float(small_sites_error["fortyTo1Hundred"]["min"])
            < expected_ttc
            <= float(small_sites_error["fortyTo1Hundred"]["max"])
        ):
            logger.debug(f"Small sites error is 40 - 100")
            return float(small_sites_error["fortyTo1Hundred"]["error"]) / expected_ttc
        else:
            logger.debug("Small sites error not found")
    else:
        logger.debug(
            f'Polygon area of {area}ha is above threshold of {params["indicators"]["wri_tropical_tree_cover"]["small_sites_area_thresh"]}'
        )
        return 0.0
