<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Role Has Been Updated</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; text-align: center; }
        .email-container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); }
        .button { display: inline-block; font-size: 16px; color: #ffffff; background: #007bff; padding: 10px 20px; border-radius: 5px; text-decoration: none; margin-top: 20px; }
        .footer { font-size: 12px; color: #666; margin-top: 20px; }
    </style>
</head>
<body>

<div class="email-container">
    <h2>Your Role Has Changed, <?= esc($user_id) ?>!</h2>
    <p>Your role has been updated to <strong><?= esc($status) ?></strong> in our system.</p>

    <p>Please log in to see any new privileges or restrictions.</p>

    <a href="https://uvluate.space/login" class="button">Login Now</a>

    <p class="footer">If you have any questions, contact support.</p>
</div>

</body>
</html>
