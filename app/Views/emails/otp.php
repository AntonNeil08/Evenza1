<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
        }
        .email-container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        .otp-box {
            display: inline-block;
            font-size: 24px;
            font-weight: bold;
            color: #ffffff;
            background: #007bff;
            padding: 12px 24px;
            border-radius: 5px;
            letter-spacing: 3px;
            margin: 15px 0;
        }
        p {
            font-size: 16px;
            color: #555;
        }
        .footer {
            font-size: 12px;
            color: #888;
            margin-top: 20px;
        }
        @media (max-width: 600px) {
            .email-container {
                width: 100%;
                padding: 15px;
            }
            .otp-box {
                font-size: 20px;
                padding: 10px 20px;
            }
        }
    </style>
</head>
<body>

<div class="email-container">
    <h2>Hello, <?= esc($full_name) ?> 👋</h2>
    <p>Your One-Time Password (OTP) for verification is:</p>

    <div class="otp-box"><?= esc($otp) ?></div>

    <p>This OTP is valid for <strong>5 minutes</strong>. Please do not share this code with anyone.</p>

    <p class="footer">
        If you didn't request this OTP, please ignore this email.<br>
        Need help? Contact our support team.
    </p>
</div>

</body>
</html>
