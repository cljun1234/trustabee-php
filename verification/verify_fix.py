from playwright.sync_api import sync_playwright
import os

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        cwd = os.getcwd()
        page.goto(f"file://{cwd}/sandbox_js_test/index.html")

        # We expect only ONE modal to be visible first
        # But since they have 0 delay, it's a race.
        # But logic should prevent overlap.

        try:
            # Check how many modals are visible
            page.wait_for_timeout(2000) # Give time for JS to run and stabilize

            visible_modals = 0
            if page.is_visible("#trustabee-coupon-modal"): visible_modals += 1
            if page.is_visible("#trustabee-announcement-modal"): visible_modals += 1

            print(f"Visible modals: {visible_modals}")

            if visible_modals == 1:
                print("SUCCESS: Only one modal is visible.")
            elif visible_modals > 1:
                 print("FAILURE: Multiple modals visible.")
            else:
                 print("FAILURE: No modals visible (or unexpected state).")

        except Exception as e:
            print(f"Error: {e}")

        # Take screenshot
        page.screenshot(path="verification/overlap_after.png")
        browser.close()

if __name__ == "__main__":
    run()
