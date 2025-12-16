from playwright.sync_api import sync_playwright
import os

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        cwd = os.getcwd()
        page.goto(f"file://{cwd}/sandbox_js_test/index.html")

        # Wait for modals to appear
        try:
            page.wait_for_selector("#trustabee-coupon-modal", state="visible", timeout=2000)
            page.wait_for_selector("#trustabee-announcement-modal", state="visible", timeout=2000)
            print("Both modals appeared.")
        except Exception as e:
            print(f"Modals status check failed (expected overlap): {e}")

        # Take screenshot
        page.screenshot(path="verification/overlap_before.png")
        browser.close()

if __name__ == "__main__":
    run()
