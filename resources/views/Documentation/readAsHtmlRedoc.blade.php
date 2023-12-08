<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>WRI Restoration Marketplace API</title>
    </head>
    <body>
        <div>
            <div class="container" style="margin-top: 80px;">
                <redoc spec-url="/documentation/{{ empty($version) ? 'raw' : $version . '/raw' }}"></redoc>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/redoc@next/bundles/redoc.standalone.js"></script>
    </body>
</html>