<!DOCTYPE html>
<html>
<head>
    <title>{{ $subject }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 10px; text-align: center; }
        .content { padding: 20px; }
        .footer { margin-top: 20px; padding: 10px; text-align: center; font-size: 0.8em; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $subject }}</h1>
        </div>

        <div class="content">
            {!! nl2br(e($body)) !!}
        </div>

        <div class="footer">
            © {{ date('Y') }} {{ config('app.name') }}. Minden jog fenntartva.
        </div>
    </div>
</body>
</html>