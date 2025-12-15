<?php
include __DIR__ . '/../layouts/header.php';
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
    margin-top: 30px;
    padding: 30px;
    background: #f4f6f8;
    border: 1px dashed #ccc;
    border-radius: 8px;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px; /* Ensure space for the popup */
}
</style>

<div class="main-content">
    <div class="page-header">
        <h1>Social Popup</h1>
    </div>

    <!-- Active Toggle Card -->
    <div class="card mb-4">
        <div class="form-group toggle" style="margin-bottom: 0; padding: 10px 0;">
            <div style="display: flex; flex-direction: column;">
                <label style="margin-bottom: 5px;">Enable Social Popup</label>
                <span style="font-size: 0.9rem; color: #666; font-weight: normal;">Show this widget on your website.</span>
            </div>
            <label class="switch">
                <input type="checkbox" onchange="toggleSocialWidget(<?php echo $social['id']; ?>, this.checked)" <?php echo $social['active'] ? 'checked' : ''; ?>>
                <span class="slider"></span>
            </label>
        </div>
    </div>

    <form action="/api/social/save" method="POST">
        <input type="hidden" name="social_id" value="<?php echo $social['id']; ?>">

        <!-- Content Settings -->
        <div class="card mb-4">
            <div class="card-header">Content & Preview</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                         <div class="mb-3">
                            <label class="form-label">Title (Badge)</label>
                            <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($social['title']); ?>" oninput="updatePreview()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subtitle</label>
                            <input type="text" class="form-control" name="subtitle" value="<?php echo htmlspecialchars($social['subtitle']); ?>" oninput="updatePreview()">
                        </div>
                        <div class="form-group toggle mt-4">
                             <label style="font-size: 1rem; font-weight: normal;">Remove Branding</label>
                             <label class="switch">
                                <input type="checkbox" name="remove_branding" id="removeBranding"
                                       <?php echo $social['remove_branding'] ? 'checked' : ''; ?> onchange="updatePreview()">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted">Live Preview</label>
                        <div class="widget-preview-container">
                            <!-- The Mock Widget -->
                            <div id="previewWidget" style="background: white; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.15); font-family: sans-serif; overflow: hidden; width: 300px;">

                                <!-- Header -->
                                <div style="padding: 15px; text-align: center; border-bottom: 1px solid #f0f0f0; position: relative;">
                                    <div style="position: absolute; top: 10px; right: 15px; color: #999;">&times;</div>
                                    <span id="previewTitle" style="background: #4ade80; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; display: inline-block; margin-bottom: 8px;">
                                        <?php echo htmlspecialchars($social['title']); ?>
                                    </span>
                                    <p id="previewSubtitle" style="margin: 0; font-size: 13px; color: #666; line-height: 1.4; padding: 0 10px;">
                                        <?php echo htmlspecialchars($social['subtitle']); ?>
                                    </p>
                                </div>

                                <!-- Links -->
                                <div id="previewLinks" style="padding: 15px;">
                                    <!-- Links injected by JS -->
                                </div>

                                <!-- Footer -->
                                <div id="previewBranding" style="text-align: center; padding-bottom: 10px; font-size: 10px; color: #1a73e8; display: <?php echo $social['remove_branding'] ? 'none' : 'block'; ?>;">
                                    Verified by Trustabee
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Links -->
        <div class="card mb-4">
            <div class="card-header">
                Social Networks
                <span class="float-end text-muted" style="font-size: 0.9rem;">Select up to 5 platforms</span>
                <span id="limit-warning" class="text-danger float-end me-2" style="display:none; font-size: 0.9rem; font-weight: bold;">Limit Reached (5/5)</span>
            </div>
            <div class="card-body">
                <div class="row">
                <?php foreach ($platforms as $platform):
                    $link = $links_map[$platform] ?? null;
                    $isActive = $link && $link['is_active'];
                    $url = $link['url'] ?? '';
                    $label = $link['label_text'] ?? "Join us on " . ucfirst($platform);
                ?>
                <div class="col-md-6 mb-3">
                    <div class="p-3 border rounded h-100" style="background: #f9f9f9;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <!-- Simple Icon Placeholder -->
                                <div style="width: 24px; height: 24px; background: #ddd; border-radius: 4px; display: flex; align-items: center; justify-content: center; margin-right: 10px; color: #555; font-weight: bold;">
                                    <?php echo strtoupper(substr($platform, 0, 1)); ?>
                                </div>
                                <span style="font-weight: 600;"><?php echo ucfirst($platform); ?></span>
                            </div>
                            <label class="switch scale-75" style="transform: scale(0.8);">
                                <input type="checkbox" class="platform-toggle"
                                       name="platform_<?php echo $platform; ?>_active"
                                       id="toggle_<?php echo $platform; ?>"
                                       data-platform="<?php echo $platform; ?>"
                                       <?php echo $isActive ? 'checked' : ''; ?>
                                       onchange="handlePlatformToggle(this)">
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div id="settings_<?php echo $platform; ?>" style="display: <?php echo $isActive ? 'block' : 'none'; ?>; margin-top: 10px;">
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-sm"
                                       name="platform_<?php echo $platform; ?>_url"
                                       placeholder="https://..."
                                       value="<?php echo htmlspecialchars($url); ?>"
                                       oninput="updatePreview()">
                            </div>
                            <div class="mb-0">
                                <input type="text" class="form-control form-control-sm"
                                       name="platform_<?php echo $platform; ?>_label"
                                       placeholder="Label (e.g. Follow us)"
                                       value="<?php echo htmlspecialchars($label); ?>"
                                       oninput="updatePreview()">
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Triggers -->
        <div class="card mb-4">
            <div class="card-header">Triggers & Rules</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                         <div class="mb-3">
                            <label class="form-label">Trigger Type</label>
                            <select class="form-select" name="trigger_type">
                                <option value="delay" <?php echo $social['trigger_type'] === 'delay' ? 'selected' : ''; ?>>Time Delay</option>
                                <option value="exit_intent" <?php echo $social['trigger_type'] === 'exit_intent' ? 'selected' : ''; ?>>Exit Intent</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Delay (seconds)</label>
                            <input type="number" class="form-control" name="trigger_delay" value="<?php echo $social['trigger_delay']; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Frequency</label>
                            <select class="form-select" name="frequency">
                                <option value="session" <?php echo $social['frequency'] === 'session' ? 'selected' : ''; ?>>Once per Session (unless closed)</option>
                                <option value="every_load" <?php echo $social['frequency'] === 'every_load' ? 'selected' : ''; ?>>Every Page Load</option>
                            </select>
                            <small class="text-muted d-block mt-1">If user clicks 'X', it will stay hidden for the session.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 mb-5">Save Changes</button>

    </form>
</div>

<script>
function toggleSocialWidget(id, enabled) {
    fetch('/api/social/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, enabled: enabled })
    });
}

function handlePlatformToggle(el) {
    const platform = el.dataset.platform;
    const settingsDiv = document.getElementById('settings_' + platform);
    settingsDiv.style.display = el.checked ? 'block' : 'none';

    validateLimit(el);
    updatePreview();
}

function validateLimit(changedEl) {
    const checked = document.querySelectorAll('.platform-toggle:checked');
    const warning = document.getElementById('limit-warning');

    if (checked.length > 5) {
        if (changedEl) changedEl.checked = false;
        // Re-hide the settings for the one we just forced off
        if (changedEl) {
             const platform = changedEl.dataset.platform;
             document.getElementById('settings_' + platform).style.display = 'none';
        }
        alert("You can only select up to 5 social networks.");
    }

    // Check again after potential reversal
    const finalChecked = document.querySelectorAll('.platform-toggle:checked');
    if (finalChecked.length >= 5) {
        warning.style.display = 'inline';
    } else {
        warning.style.display = 'none';
    }
}

function updatePreview() {
    // Texts
    document.getElementById('previewTitle').textContent = document.querySelector('input[name="title"]').value;
    document.getElementById('previewSubtitle').textContent = document.querySelector('input[name="subtitle"]').value;

    // Branding
    const removeBranding = document.getElementById('removeBranding').checked;
    document.getElementById('previewBranding').style.display = removeBranding ? 'none' : 'block';

    // Links
    const linksContainer = document.getElementById('previewLinks');
    linksContainer.innerHTML = '';

    const platforms = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'tiktok', 'pinterest', 'whatsapp', 'telegram'];

    platforms.forEach(p => {
        const toggle = document.getElementById('toggle_' + p);
        if (toggle && toggle.checked) {
            const url = document.querySelector(`input[name="platform_\${p}_url"]`).value;
            const label = document.querySelector(`input[name="platform_\${p}_label"]`).value || p;

            const item = document.createElement('div');
            item.style.cssText = `
                display: flex; align-items: center; text-decoration: none;
                padding: 10px; margin-bottom: 8px; border: 1px dashed #ddd;
                border-radius: 8px; color: #333; font-size: 14px; background: white;
            `;

            let iconColor = '#333';
            if (p === 'facebook') iconColor = '#1877f2';
            if (p === 'twitter') iconColor = '#1da1f2';
            if (p === 'instagram') iconColor = '#c32aa3';
            if (p === 'linkedin') iconColor = '#0a66c2';
            if (p === 'youtube') iconColor = '#ff0000';
            if (p === 'whatsapp') iconColor = '#25d366';
            if (p === 'tiktok') iconColor = '#000000';
            if (p === 'pinterest') iconColor = '#bd081c';
            if (p === 'telegram') iconColor = '#0088cc';

            item.innerHTML = `
                <span style="width: 24px; height: 24px; background: \${iconColor}; border-radius: 4px; margin-right: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    \${p.charAt(0).toUpperCase()}
                </span>
                <span style="font-weight: 500;">\${label}</span>
            `;

            linksContainer.appendChild(item);
        }
    });
}

// Init
document.addEventListener('DOMContentLoaded', () => {
    updatePreview();
    // Run limit check initially
    const warning = document.getElementById('limit-warning');
    const checked = document.querySelectorAll('.platform-toggle:checked');
    if (checked.length >= 5) warning.style.display = 'inline';
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
