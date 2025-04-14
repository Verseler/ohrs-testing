<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmation</title>
    <style>
        body {
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 25px;
            background-color: #f0fdf4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-top: 4px solid #10b981;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 20px;
        }
        h2 {
            color: #10b981;
            margin-top: 0;
        }
        .reservation-code {
            background-color: #f0fdf9;
            border: 1px solid #ccfbf1;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
        }
        .code {
            font-size: 28px;
            font-weight: bold;
            color: #0d9488;
            letter-spacing: 2px;
            margin: 15px 0;
            padding: 10px 15px;
            background-color: white;
            border-radius: 4px;
            display: inline-block;
            border: 1px dashed #10b981;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #64748b;
        }
        .highlight-box {
            background-color: #ecfdf5;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 14px;
            border-left: 4px solid #10b981;
        }
        a {
            color: #0d9488;
            text-decoration: none;
            font-weight: 500;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h2>Reservation Confirmed</h2>
            <p style="color: #64748b;">Thank you for using the Online Hostel Reservation System</p>
        </div>

        <p>Dear Guest,</p>

        <p>Your reservation has been successfully confirmed. Below are your reservation details:</p>

        <div class="reservation-code">
            <p style="font-weight: 600; margin-bottom: 5px; color: #0f766e;">Reservation Code</p>
            <div class="code">{{ $data['content'] }}</div>
            <p style="font-size: 13px; color: #64748b;">Please keep this code for your records</p>
        </div>

        <div class="highlight-box">
            <p style="color: #0f766e; font-weight: 600; margin-top: 0;">Important: You'll need this code to:</p>
            <ul style="margin-top: 8px; padding-left: 20px; color: #334155;">
                <li>View your reservation status</li>
                <li>Modify your booking</li>
                <li>Check-in at the hostel</li>
                <li>Access your booking details</li>
            </ul>
        </div>


        <div class="footer">
            <p><strong style="color: #0f766e;">DENR 10 - Online Hostel Reservation System</strong></p>
            <p>Department of Environment and Natural Resources</p>
            <p style="font-size: 12px; margin-top: 15px; color: #94a3b8;">This is an automated message. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>
