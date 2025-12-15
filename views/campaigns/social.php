<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Social Popup</h1>
    </div>

    <!-- Active Toggle Card -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">Enable Social Popup</h5>
                    <p class="text-muted mb-0">Show this widget on your website</p>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="activeToggle"
                           <?php echo $social['active'] ? 'checked' : ''; ?>
                           onchange="toggleSocialWidget(<?php echo $social['id']; ?>, this.checked)">
                </div>
            </div>
        </div>
    </div>

    <form action="/api/social/save" method="POST">
        <input type="hidden" name="social_id" value="<?php echo $social['id']; ?>">

        <div class="row">
            <!-- Left Column: Settings -->
            <div class="col-md-7">

                <!-- Content Settings -->
                <div class="card mb-4">
                    <div class="card-header">Content Customization</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Title (Badge)</label>
                            <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($social['title']); ?>" oninput="updatePreview()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subtitle</label>
                            <input type="text" class="form-control" name="subtitle" value="<?php echo htmlspecialchars($social['subtitle']); ?>" oninput="updatePreview()">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="remove_branding" id="removeBranding"
                                   <?php echo $social['remove_branding'] ? 'checked' : ''; ?> onchange="updatePreview()">
                            <label class="form-check-label" for="removeBranding">Remove Branding</label>
                        </div>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="card mb-4">
                    <div class="card-header">
                        Social Networks (Max 5)
                        <span id="limit-warning" class="text-danger float-end" style="display:none; font-size: 0.8rem;">Max 5 allowed!</span>
                    </div>
                    <div class="card-body">
                        <?php foreach ($platforms as $platform):
                            $link = $links_map[$platform] ?? null;
                            $isActive = $link && $link['is_active'];
                            $url = $link['url'] ?? '';
                            $label = $link['label_text'] ?? "Join us on " . ucfirst($platform);
                        ?>
                        <div class="social-item mb-3 p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-2"><?php echo ucfirst($platform); ?></span>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input platform-toggle" type="checkbox"
                                           name="platform_<?php echo $platform; ?>_active"
                                           id="toggle_<?php echo $platform; ?>"
                                           data-platform="<?php echo $platform; ?>"
                                           <?php echo $isActive ? 'checked' : ''; ?>
                                           onchange="handlePlatformToggle(this)">
                                </div>
                            </div>

                            <div id="settings_<?php echo $platform; ?>" style="display: <?php echo $isActive ? 'block' : 'none'; ?>">
                                <div class="mb-2">
                                    <input type="text" class="form-control form-control-sm"
                                           name="platform_<?php echo $platform; ?>_url"
                                           placeholder="<?php echo ucfirst($platform); ?> URL"
                                           value="<?php echo htmlspecialchars($url); ?>"
                                           oninput="updatePreview()">
                                </div>
                                <div class="mb-2">
                                    <input type="text" class="form-control form-control-sm"
                                           name="platform_<?php echo $platform; ?>_label"
                                           placeholder="Button Label"
                                           value="<?php echo htmlspecialchars($label); ?>"
                                           oninput="updatePreview()">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Triggers -->
                <div class="card mb-4">
                    <div class="card-header">Triggers & Rules</div>
                    <div class="card-body">
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
                        <div class="mb-3">
                            <label class="form-label">Frequency</label>
                            <select class="form-select" name="frequency">
                                <option value="session" <?php echo $social['frequency'] === 'session' ? 'selected' : ''; ?>>Once per Session (unless closed)</option>
                                <option value="every_load" <?php echo $social['frequency'] === 'every_load' ? 'selected' : ''; ?>>Every Page Load</option>
                            </select>
                            <small class="text-muted">Note: "Close it for the session only" behavior applies if user clicks X.</small>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mb-5">Save Changes</button>

            </div>

            <!-- Right Column: Preview -->
            <div class="col-md-5">
                <div class="sticky-top" style="top: 20px;">
                    <h5 class="mb-3">Live Preview</h5>
                    <div class="preview-container" style="position: relative; height: 600px; background: #f4f6f8; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">

                        <!-- The Mock Widget -->
                        <div id="previewWidget" style="position: absolute; bottom: 20px; right: 20px; width: 300px; background: white; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.15); font-family: sans-serif; overflow: hidden;">

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
        warning.style.display = 'inline';
        // Hide settings if we force unchecked
        if (changedEl) {
             const platform = changedEl.dataset.platform;
             document.getElementById('settings_' + platform).style.display = 'none';
        }
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
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
