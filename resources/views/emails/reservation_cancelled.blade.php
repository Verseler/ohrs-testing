<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Cancellation Notice</title>
</head>
<body>
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #e74c3c; font-size: 24px; margin-bottom: 20px;">Reservation Cancellation Notice</h1>
        </div>

        <div style="background-color: #f8f9fa; padding: 25px; border-radius: 8px;">
            <p style="margin-bottom: 15px; line-height: 1.6;">Dear {{ $guestName }},</p>

            <p style="margin-bottom: 15px; line-height: 1.6;">
                We regret to inform you that your reservation <span>{{ $data['content'] }}</span>
                has been cancelled by hostel administration.
            </p>

            <p style="margin-bottom: 0; line-height: 1.6;">
                We apologize for any inconvenience this may have caused and hope to serve you
                better in the future.
            </p>
        </div>

        <div class="footer">
            <p><strong style="color: #0f766e;">DENR 10 - Online Hostel Reservation System</strong></p>
            <p>Department of Environment and Natural Resources</p>
            <p style="font-size: 12px; margin-top: 15px; color: #94a3b8;">This is an automated message. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>
