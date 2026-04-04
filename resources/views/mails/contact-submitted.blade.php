<!DOCTYPE html>
<html>
<head>
    <title>New Contact Message - {{ config('app.name') }}</title>
</head>
<body>
    <h1>New Contact Message from {{ $name }}</h1>
    <p>You have received a new message through the contact form.</p>

    <p><strong>Name:</strong> {{ $name }}</p>
    <p><strong>Email:</strong> {{ $email }}</p>
    <p><strong>Subject:</strong> {{ $subject }}</p>

    <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border: 1px solid #dee2e6;">
        <p><strong>Message:</strong></p>
        <p>{{ $message_body }}</p>
    </div>

    <br>
    <p>Please login to the administrative dashboard to reply to this message.</p>
    <br>
    <p>Thank you,<br>{{ config('app.name') }} System</p>
</body>
</html>
