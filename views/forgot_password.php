<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        h1 { margin-top: 0; color: #333; }
        p { color: #666; margin-bottom: 20px; }
        input { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #0084ff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #006bcf; }
        .error { color: #dc3545; margin-bottom: 10px; font-size: 0.9rem; }
        .success { color: #28a745; margin-bottom: 10px; font-size: 0.9rem; }
        .link { display: block; margin-top: 15px; color: #0084ff; text-decoration: none; font-size: 0.9rem; }
        .link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Forgot Password</h1>
        <p>Enter your email to reset your password.</p>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="/forgot-password" method="POST">
            <input type="email" name="email" placeholder="Email Address" required>
            <button type="submit">Send Reset Link</button>
        </form>

        <a href="/login" class="link">Back to Login</a>
    </div>
</body>
</html>
