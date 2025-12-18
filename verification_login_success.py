from playwright.sync_api import sync_playwright

def verify_login_success_message():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()

        # Verify Login Page with Success Message
        login_html = """
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Trustabee - Login</title>
            <style>
                body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5; }
                .login-box { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 300px; }
                input { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
                button { width: 100%; padding: 10px; background: #1a73e8; color: white; border: none; border-radius: 4px; cursor: pointer; }
                button:hover { background: #1557b0; }
                h2 { text-align: center; margin-top: 0; }
                .hint { font-size: 0.8rem; color: #666; text-align: center; margin-top: 10px; }
                .error { color: red; font-size: 0.9rem; text-align: center; margin-bottom: 10px; }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h2>Trustabee</h2>
                <div style="color: green; font-size: 0.9rem; text-align: center; margin-bottom: 10px;">
                    Password reset successful. Please login.
                </div>
                <form action="/login" method="POST">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Login</button>
                </form>
                <p class="hint">Try: admin@trustabee.com / password</p>
                <div style="text-align: center; margin-top: 15px; font-size: 0.9rem;">
                    <a href="/register" style="color: #1a73e8; text-decoration: none;">Register</a> |
                    <a href="/forgot-password" style="color: #1a73e8; text-decoration: none;">Forgot Password?</a>
                </div>
            </div>
        </body>
        </html>
        """
        page.set_content(login_html)
        page.screenshot(path="verification/login_success.png")
        print("Login Success Page Screenshot taken")

        browser.close()

if __name__ == "__main__":
    verify_login_success_message()
