<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        h1 { margin-top: 0; color: #333; }
        input { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #0084ff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #006bcf; }
        .error { color: #dc3545; margin-bottom: 10px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Reset Password</h1>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="/reset-password" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
            <input type="password" name="password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>
