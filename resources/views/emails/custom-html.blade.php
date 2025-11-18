<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: {{ $bodyBgColor ?? '#f4f4f4' }};
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
        }
        .btn-primary {
            background-color: {{ $primaryColor ?? '#635bff' }};
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
            font-weight: 600;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
    </style>
</head>
<body>
    {!! $content !!}
</body>
</html>