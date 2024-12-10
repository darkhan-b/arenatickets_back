<!DOCTYPE html>
<html lang="ru">
<head>
    <title>@yield('title','Документ')</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: 'PTSans';
            font-style: normal;
            font-weight: normal;
            {{--src: url('{{ public_path("fonts/Lucida.ttf") }}') format('truetype');--}}
            src: url('{{ public_path("fonts/PTSans-Regular.ttf") }}') format('truetype');
        }
        body {
            font-family: 'PTSans';
        }
        /*body {*/
        /*    font-family: DejaVu Sans;*/
        /*}*/
        .w-100 {
            width: 100%;
        }
        .mt-20 {
            margin-top: 20px;
        }
        .mb-1 {
            margin-bottom: 10px;
        }
        .mt-1 {
            margin-top: 10px;
        }
        .mt-2 {
            margin-top: 20px;
        }
        .pl-4 {
            padding-left: 40px;
        }
        .pr-4 {
            padding-right: 40px;
        }
        .page-break {
            page-break-after: always;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .col-6 {
            display: inline-block;
            width: 50%;
            vertical-align: top;
        }
        .border-bottom  {
            border-bottom: 1px solid #333;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid;
            padding: 4px;
        }
        @page {
            margin:0;
        }
        small {
            font-size: 50%;
        }
    </style>
</head>
<body>
@yield('content')
</body>
</html>
