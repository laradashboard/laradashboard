<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribed - {{ config('app.name') }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; text-align: center; padding: 50px;">
    <h1>Successfully Unsubscribed</h1>
    <p>You will no longer receive emails from us.</p>
    <a href="{{ url('/') }}">Return to Homepage</a>
</body>
</html>