<!DOCTYPE html>
<html>
<head>
    <title>Reply from {{ config('app.name') }}</title>
</head>
<body>
    <h1>Hi, {{ $name }}!</h1>
    <p>Thank you for contacting us. Here is the response to your message regarding: <strong>{{ $subject }}</strong>.</p>

    <div style="margin-top: 20px; padding: 15px; background-color: #f1f1f1; border-left: 5px solid #28a745;">
        <p><strong>Response:</strong></p>
        <p>{{ $reply }}</p>
    </div>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">

    <p style="color: #6c757d;"><strong>Your Original Message:</strong></p>
    <blockquote style="margin-left: 0; padding-left: 15px; border-left: 3px solid #dee2e6; color: #6c757d;">
        {{ $message }}
    </blockquote>

    <br>
    <p>Best regards,<br>{{ config('app.name') }} Team</p>
</body>
</html>
