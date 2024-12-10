<!DOCTYPE html>
<html>
<head>
    <title>Arenatickets - documentation</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700|Roboto:300,400,700" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
<redoc spec-url='{{ env('APP_URL') }}/api-docs/tourniquet-docs.json?v=9' expand-responses="true"></redoc>
<script src="/js/vendor/redoc.standalone.js"> </script>
</body>
</html>
