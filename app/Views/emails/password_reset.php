<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .email-container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; text-align: center; }
        .password-box { font-size: 20px; font-weight: bold; color: #ffffff; background: #007bff; padding: 10px 20px; border-radius: 5px; }
        .footer { font-size: 12px; color: #666; margin-top: 20px; }
    </style>
</head>
<body>

<div class="email-container">
    <h2>Password Reset</h2>
    <p>Your password has been reset. Use the following temporary password to log in:</p>

    <div class="password-box"><?= esc($temp_password) ?></div>

    <p>You will be required to change your password after logging in.</p>

    <p class="footer">If you did not request this reset, please contact support immediately.</p>
</div>

</body>
</html>
