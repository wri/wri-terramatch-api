import shapely

def get_gfw_data(polygon, session, indicator_type, params):
    url = f'{params["gfw_api"]["base_url"]}{params["indicators"][indicator_type]["query_url"]}'
    sql = params["indicators"][indicator_type]["sql"]
    payload = {"sql": sql, "geometry": shapely.geometry.mapping(polygon)}
    with session.post(url, json=payload) as response:
        if not response.ok:
            raise RuntimeError(f"{response.status_code}")
        response_data = response.json()
    return response_data


class UnsupportedGFWLayer(Exception):
    def __init__(self, estimator_name):
        self.msg = f"Unsupported GFW layer {estimator_name}"
        super().__init__(self.msg)


def get_supported_gfw_layer():
    return {
        "umd_tree_cover_loss": "umd_tree_cover_loss",
        "umd_tree_cover_loss_from_fires": "umd_tree_cover_loss_from_fires",
        "wwf_terrestrial_ecoregions": "wwf_terrestrial_ecoregions",
        "wri_tropical_tree_cover": "wri_tropical_tree_cover",
    }
