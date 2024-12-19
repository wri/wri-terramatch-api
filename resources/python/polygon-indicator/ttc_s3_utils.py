import pandas as pd
import os
from shapely.geometry import shape
from boto3 import client
from botocore.exceptions import ClientError
from osgeo import gdal

pd.reset_option("mode.chained_assignment")


def build_tile_lookup(params):
    bucket_name = params["s3"]["lookup_bucket"]
    lookup_file_list = params["s3"]["lookup_filelist"]
    outpath = params["base"]["temp_path"]
    if params["indicators"]["wri_tropical_tree_cover"]["download_tile_lookup"]:
        conn = client("s3")
        for file in lookup_file_list:
            conn.download_file(
                Bucket=bucket_name,
                Key=file,
                Filename=(outpath + os.path.basename(file)),
            )
    df_list = []
    for file in lookup_file_list:
        filename = outpath + os.path.basename(file)
        df = pd.read_csv(filename, index_col=None, header=0)
        df_list.append(df)
    global_lookup = pd.concat(df_list, axis=0, ignore_index=True)
    return global_lookup


def pre_filter_tiles_feature(feature, global_lookup):
    centroid = shape(feature.geometry).centroid
    poly_x = centroid.x
    poly_y = centroid.y
    pre_filter = global_lookup[
        (abs(global_lookup["X"] - poly_x) < 0.1)
        & (abs(global_lookup["Y"] - poly_y) < 0.1)
    ]
    pre_filter["X_tile"] = pd.to_numeric(pre_filter["X_tile"], downcast="integer")
    pre_filter["Y_tile"] = pd.to_numeric(pre_filter["Y_tile"], downcast="integer")
    return pre_filter


def pre_filter_tiles_project(project_gdf, global_lookup):
    bounds = project_gdf.total_bounds
    pre_filter = global_lookup[
        (global_lookup["X"] > (bounds[0] - 0.05))
        & (global_lookup["X"] < (bounds[2] + 0.05))
        & (global_lookup["Y"] > (bounds[1] - 0.05))
        & (global_lookup["Y"] < (bounds[3] + 0.05))
    ]
    pre_filter["X_tile"] = pd.to_numeric(pre_filter["X_tile"], downcast="integer")
    pre_filter["Y_tile"] = pd.to_numeric(pre_filter["Y_tile"], downcast="integer")
    return pre_filter


def build_bucket_path(x_tile, y_tile, config):
    filename = f"{config['indicators']['wri_tropical_tree_cover']['data_year']}/tiles/{x_tile}/{y_tile}/{x_tile}X{y_tile}Y_FINAL.tif"
    return filename


def download_tiles(feature, global_lookup, type, params):
    conn = client("s3")
    if type == "polygon":
        pre_filtered_lookup = pre_filter_tiles_feature(feature, global_lookup)
    elif type == "project":
        pre_filtered_lookup = pre_filter_tiles_project(feature, global_lookup)
    tile_file_list = list(
        pre_filtered_lookup.apply(
            lambda row: build_bucket_path(row["X_tile"], row["Y_tile"], params), axis=1
        )
    )
    bucket_name = params["s3"]["tile_bucket"]
    outpath = params["base"]["temp_path"]
    directory = f"{outpath}tiles/"
    if not os.path.exists(directory):
        os.makedirs(directory)
    tile_list = []
    for file in tile_file_list:
        try:
            conn.download_file(
                Bucket=bucket_name,
                Key=file,
                Filename=(directory + os.path.basename(file)),
            )
            tile_list.append(directory + os.path.basename(file))
        except ClientError as e:
            print(e)
    return tile_list


def make_mosaic(file_list, project_name, params):
    gdal.BuildVRT(
        f"{str(params['gdal']['outfile'])}.vrt",
        file_list,
        options=gdal.BuildVRTOptions(srcNodata=255, VRTNodata=255),
    )
    ds = gdal.Open(f"{str(params['gdal']['outfile'])}.vrt")
    translateoptions = gdal.TranslateOptions(
        gdal.ParseCommandLine("-ot Byte -co COMPRESS=LZW -a_nodata 255 -co BIGTIFF=YES")
    )
    ds = gdal.Translate(
        f"{str(params['gdal']['outfile'])}-{project_name}-{str(params['gdal']['outfile_suffix'])}.tif",
        ds,
        options=translateoptions,
    )
    os.remove(f"{str(params['gdal']['outfile'])}.vrt")
