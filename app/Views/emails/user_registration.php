<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to the Application</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; text-align: center; }
        .email-container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); }
        .credentials { font-size: 16px; margin-top: 20px; text-align: left; }
        .button { display: inline-block; font-size: 16px; color: #ffffff; background: #007bff; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin-top: 20px; }
        .footer { font-size: 12px; color: #666; margin-top: 20px; }
    </style>
</head>
<body>

<div class="email-container">
    <h2>Welcome, <?= esc($full_name) ?> 👋</h2>
    <p>You have been successfully registered as a <strong><?= esc($role) ?></strong> in our system.</p>

    <p class="credentials">
        <strong>Your login credentials:</strong><br>
        <strong>User ID:</strong> <?= esc($user_id) ?><br>
        <strong>Password:</strong> <?= esc($password) ?><br>
    </p>

    <p>Please log in to your account to access your dashboard.</p>

    <a href="https://your-frontend-url.com/login" class="button">Login Now</a>

    <p class="footer">If you have any questions, contact support.</p>
</div>

</body>
</html>
