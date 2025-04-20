<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outstanding Balance Notice</title>
    <style>
        body {
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        h2 {
            color: #0f766e;
            margin-top: 0;
        }
        .balance-display {
            background-color: #f0fdf9;
            border: 1px solid #ccfbf1;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
        }
        .balance-amount {
            font-size: 24px;
            font-weight: bold;
            color: #0d9488;
            margin: 10px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h2>Outstanding Balance Notice</h2>
            <p style="color: #64748b;">Please settle your remaining balance</p>
        </div>

        <p>Dear Guest,</p>

        <p>Your reservation <strong>{{ $data['reservation_code'] }}</strong> has an outstanding balance as of {{ $data['date'] }}:</p>

        <div class="balance-display">
            <p style="font-weight: 600; margin-bottom: 5px; color: #0f766e;">Remaining Balance</p>
            <div class="balance-amount">₱{{ number_format($data['remaining_balance'], 2) }}</div>
            <p style="font-size: 13px; color: #64748b;">Please settle this amount before your check-in date</p>
        </div>

        <div class="footer">
            <p><strong>DENR 10 - Online Hostel Reservation System</strong></p>
            <p>Department of Environment and Natural Resources</p>
            <p style="font-size: 12px; margin-top: 15px;">This is an automated message. Please do not reply.</p>
        </div>
    </div>
</body>
</html>