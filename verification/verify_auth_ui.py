from playwright.sync_api import sync_playwright
import os

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()

        # Verify Login UI
        login_path = os.path.abspath("verification/mock_login.html")
        page.goto(f"file://{login_path}")
        page.screenshot(path="verification/login_ui.png")
        print("Login UI screenshot captured.")

        # Verify Register UI
        register_path = os.path.abspath("verification/mock_register.html")
        page.goto(f"file://{register_path}")
        page.screenshot(path="verification/register_ui.png")
        print("Register UI screenshot captured.")

        browser.close()

if __name__ == "__main__":
    run()
