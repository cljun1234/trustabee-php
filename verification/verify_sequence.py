from playwright.sync_api import sync_playwright
import os

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        cwd = os.getcwd()
        page.goto(f"file://{cwd}/sandbox_js_test/index.html")

        # 1. Wait for ANY modal to appear
        # Since both have 0 delay, one should win.
        page.wait_for_timeout(2000)

        visible_count = 0
        visible_id = None
        if page.is_visible("#trustabee-coupon-modal"):
            visible_count += 1
            visible_id = "#trustabee-coupon-modal"
        if page.is_visible("#trustabee-announcement-modal"):
            visible_count += 1
            visible_id = "#trustabee-announcement-modal"

        print(f"Visible modals initially: {visible_count}")
        if visible_count != 1:
            print("FAILURE: Expected exactly 1 modal initially.")
            return

        # 2. Close the visible modal
        # I added an ID to the close button in widget.js for easier selection in test: `containerId + '-close'`
        close_btn = visible_id + "-close"
        print(f"Closing modal: {visible_id}")
        page.click(close_btn)

        # 3. Wait for the other modal to appear
        # It should retry every 1000ms.
        # Wait enough time for retry logic to kick in.
        print("Waiting for second modal...")
        page.wait_for_timeout(2000)

        # Check if the OTHER modal appeared
        # Since we don't know which one appeared first (race), we check both again.
        # But specifically, we expect *a* modal to be visible now (the one that was queued).

        visible_count_2 = 0
        if page.is_visible("#trustabee-coupon-modal"): visible_count_2 += 1
        if page.is_visible("#trustabee-announcement-modal"): visible_count_2 += 1

        print(f"Visible modals after close: {visible_count_2}")

        if visible_count_2 == 1:
             print("SUCCESS: Second modal appeared after first closed.")
        else:
             print("FAILURE: Second modal did not appear (or multiple appeared).")

        # Take screenshot
        page.screenshot(path="verification/overlap_sequence.png")
        browser.close()

if __name__ == "__main__":
    run()
