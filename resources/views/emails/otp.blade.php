<!DOCTYPE html>
<html>
<head>
    <title>Password Reset OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #007BFF;
        }
        .content {
            text-align: center;
            margin: 20px 0;
        }
        .otp {
            font-size: 36px;
            font-weight: bold;
            color: #007BFF;
            letter-spacing: 3px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password Reset OTP</h1>
        </div>
        <div class="content">
            <p>Dear User,</p>
            <p>Here is your OTP for password reset:</p>
            <p class="otp">{{ $otp }}</p>
            <p>Please use this OTP to reset your password. It will expire in 10 minutes.</p>
        </div>
        <div class="footer">
            <p>If you did not request a password reset, please ignore this email.</p>
        </div>
    </div>
</body>
</html>
