<?php
$pageTitle = 'Live Visitors';
$activePage = 'campaigns';
$activeSubPage = 'live-visitors';
require_once __DIR__ . '/../layouts/header.php';

$settings = $config['settings'] ?? [];
$pos = $settings['position'] ?? 'bottom-left';
$bg = $settings['bg_color'] ?? '#ffffff';
$txt = $settings['text_color'] ?? '#333333';
?>

<style>
/* Switch Toggle CSS */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 26px;
}
.switch input { opacity: 0; width: 0; height: 0; }
.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 34px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 3px; bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}
input:checked + .slider { background-color: var(--primary-color); }
input:focus + .slider { box-shadow: 0 0 1px var(--primary-color); }
input:checked + .slider:before { transform: translateX(24px); }
.form-group.toggle { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.form-group.toggle label { margin-bottom: 0; font-size: 1.1rem; font-weight: 600; }

/* Widget Preview Styles */
.widget-preview-container {
    margin-top: 20px;
    padding: 20px;
    background: #f4f6f8;
    border: 1px dashed #ccc;
    border-radius: 8px;
    display: flex;
    justify-content: center;
    align-items: center;
}
.sales-notification-widget {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    padding: 10px;
    display: flex;
    align-items: center;
    width: 300px;
    font-family: sans-serif;
    color: #333;
    /* Static positioning for preview */
    position: relative;
}
.sales-notification-widget .map-placeholder { width: 50px; height: 50px; background: #eee; border-radius: 4px; margin-right: 10px; flex-shrink: 0; overflow: hidden; }
.sales-notification-widget .map-placeholder img { width: 100%; height: 100%; object-fit: cover; }
.sales-notification-widget .content { display: flex; flex-direction: column; justify-content: center; flex-grow: 1; line-height: 1.2; }
.sales-notification-widget .name { font-weight: 700; color: inherit; font-size: 14px; margin: 0; }
.sales-notification-widget .action-text { margin: 2px 0 5px 0; color: inherit; opacity: 0.8; font-size: 13px; }
.sales-notification-widget .verification { display: flex; align-items: center; font-size: 11px; color: #1a73e8; font-weight: 500; }
.sales-notification-widget .checkmark { font-weight: bold; margin-right: 4px; }
</style>

<!-- Full Width Enable/Disable Card -->
<div class="card">
    <div class="form-group toggle" style="margin-bottom: 0; padding: 10px 0;">
        <div style="display: flex; flex-direction: column;">
            <label style="margin-bottom: 5px;">Enable Live Visitor Widget</label>
            <span style="font-size: 0.9rem; color: #666; font-weight: normal;">Display the "Active Visitors" counter on your site.</span>
        </div>
        <label class="switch">
            <input type="checkbox" onchange="toggleFeature('live_visitor', this.checked)" <?php echo $config['enabled'] ? 'checked' : ''; ?>>
            <span class="slider"></span>
        </label>
    </div>
</div>

<!-- Styling Configuration Card -->
<div class="card">
    <h2>Styling Configuration</h2>
    <form action="/save-live-visitor-config" method="POST">
        <input type="hidden" name="widget_id" value="<?php echo $widget['id']; ?>">
        <!-- Note: We keep the 'enabled' check in PHP logic just in case, but rely on AJAX above -->

        <div class="form-group">
            <label>Position</label>
            <select name="position" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                <option value="bottom-left" <?php echo $pos == 'bottom-left' ? 'selected' : ''; ?>>Bottom Left</option>
                <option value="bottom-right" <?php echo $pos == 'bottom-right' ? 'selected' : ''; ?>>Bottom Right</option>
            </select>
        </div>

        <div class="form-group">
            <label>Background Color</label>
            <input type="color" id="bgInput" name="bg_color" value="<?php echo htmlspecialchars($bg); ?>" style="width: 100%; height: 40px;">
        </div>

         <div class="form-group">
            <label>Text Color</label>
            <input type="color" id="txtInput" name="text_color" value="<?php echo htmlspecialchars($txt); ?>" style="width: 100%; height: 40px;">
        </div>

        <div class="widget-preview-container">
            <div id="previewWidget" class="sales-notification-widget" style="background-color: <?php echo htmlspecialchars($bg); ?>; color: <?php echo htmlspecialchars($txt); ?>;">
                <!-- Live Visitor Mode: No Map, just text -->
                 <div class="content" style="width: 100%;">
                    <p class="name">Live Visitors</p>
                    <p class="action-text">24 people are viewing this page right now.</p>
                </div>
            </div>
        </div>

        <button type="submit" class="btn" style="margin-top: 15px;">Save Appearance</button>
    </form>
</div>

<div id="toast" style="visibility: hidden; min-width: 250px; margin-left: -125px; background-color: #333; color: #fff; text-align: center; border-radius: 2px; padding: 16px; position: fixed; z-index: 1; left: 50%; bottom: 30px; font-size: 17px;">
  Setting updated successfully!
</div>

<script>
    const WIDGET_ID = <?php echo json_encode($widget['id']); ?>;

    async function toggleFeature(featureName, isEnabled) {
        try {
            const response = await fetch('/api/toggle-feature', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    feature: featureName,
                    enabled: isEnabled,
                    widget_id: WIDGET_ID
                })
            });
            const text = await response.text();
            try {
                const json = JSON.parse(text);
                if (json.success) {
                    showToast();
                } else {
                    console.error('API Error:', json);
                    alert('Failed to update setting: ' + (json.error || 'Unknown error'));
                }
            } catch (jsonError) {
                console.error('JSON Parse Error:', jsonError, text);
                alert('Server returned invalid JSON. Check console.');
            }
        } catch (e) {
            console.error(e);
            alert('Error updating setting.');
        }
    }

    function showToast() {
      var x = document.getElementById("toast");
      x.style.visibility = "visible";
      setTimeout(function(){ x.style.visibility = "hidden"; }, 3000);
    }

    // Preview Logic
    const bgInput = document.getElementById('bgInput');
    const txtInput = document.getElementById('txtInput');
    const previewWidget = document.getElementById('previewWidget');

    bgInput.addEventListener('input', function() {
        previewWidget.style.backgroundColor = this.value;
    });

    txtInput.addEventListener('input', function() {
        previewWidget.style.color = this.value;
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
