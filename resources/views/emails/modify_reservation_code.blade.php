<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Modification Code</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .code-container {
            background-color: #f5f9ff;
            border: 1px solid #d6e4ff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
        }

        .verification-code {
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #10b981;
            margin: 10px 0;
            padding: 10px 20px;
            background-color: white;
            border-radius: 6px;
            display: inline-block;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
            font-size: 14px;
            color: #777777;
            text-align: center;
        }

        .instructions {
            background-color: #ecfdf5;
            padding: 15px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <!-- Replace with actual DENR logo URL -->
        <h2 style="color: #10b981; margin-bottom: 5px;">Reservation Modification</h2>
        <p style="margin-top: 0;">Your verification code is required to proceed</p>
    </div>

    <div class="instructions">
        <p>To modify your hostel reservation, please use the following verification code:</p>
    </div>

    <div class="code-container">
        <p style="margin-bottom: 10px; font-weight: 500;">Verification Code</p>
        <div class="verification-code">{{ $data['content'] }}</div>
        <p style="font-size: 13px; color: #666;">This code will expire in 5 minutes</p>
    </div>

    <p style="font-size: 14px; color: #666;">If you didn't request this code, please ignore this email or contact
        support if you have concerns.</p>

    <div class="footer">
        <p>DENR 10 - Online Hostel Reservation System<br>
            <span style="font-size: 12px;">Department of Environment and Natural Resources</span>
        </p>
    </div>
</body>

</html>
