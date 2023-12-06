<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>WRI Restoration Marketplace API</title>
        <link rel="stylesheet" href="/css/swagger-ui.css">
        <script src="/js/swagger-ui-bundle.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function(event) {
                var swagger = SwaggerUIBundle({
                    url:  "/documentation/{{ empty($version) ? '' : "$version/"  }}raw",
                    dom_id: "#swagger-ui",
                    supportedSubmitMethods: [],
                    docExpansion: "none",
                    tagsSorter: "alpha",
                    validatorUrl: "https://online.swagger.io/validator"
                });
            });
        </script>
        <style>
            /* hide the authorize button... it doesn't work with bearer auth */
            .auth-wrapper {
                display: none !important;
            }
            .models {
                display: none !important;
            }
        </style>
    </head>
    <body>
        <div id="swagger-ui"></div>
    </body>
</html>