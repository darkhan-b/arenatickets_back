<!DOCTYPE html>
<html>
<head>
    <title>Arenatickets - documentation</title>
    <!-- needed for adaptive design -->
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700|Roboto:300,400,700" rel="stylesheet">

    <!--
    Redoc doesn't change outer page styles
    -->
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
<redoc spec-url='{{ env('APP_URL') }}/api-docs/api-docs.json?v=7' expand-responses="true"></redoc>
<script src="/js/vendor/redoc.standalone.js"> </script>
</body>
</html>
