<?php
$activePage = 'settings';
$pageTitle = 'Settings';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Installation -->
<div class="card">
    <h2>Installation</h2>
    <p>Copy and paste this code into the <code>&lt;head&gt;</code> of your website.</p>
    <?php
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $scriptUrl = $protocol . $host . '/api/widget.js?w=' . $widget['id'];
    ?>
    <textarea class="code-block" readonly><script src="<?php echo $scriptUrl; ?>"></script></textarea>
</div>

<!-- Configuration -->
<div class="card">
    <h2>Configuration</h2>
    <form action="/widget/save" method="POST">
        <div class="form-group" style="margin-top: 20px;">
            <label>Allowed Domains</label>
            <p style="font-size: 0.8rem; color: #666; margin-top: 0;">
                Limit: <span id="domain-limit-text"><?php echo ($plan['domain_limit'] == -1) ? 'Unlimited' : $plan['domain_limit']; ?></span>.
                Only these domains are allowed to load the widget.
            </p>

            <div id="domain-list" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px;"></div>

            <div style="display: flex; gap: 10px;">
                <input type="text" id="new-domain-input" placeholder="example.com" style="flex: 1; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                <button type="button" id="add-domain-btn" class="btn" onclick="addDomain()" style="background: #28a745;">Add</button>
            </div>
            <input type="hidden" name="allowed_domains" id="allowed-domains-hidden" value="<?php echo htmlspecialchars($widget['allowed_domains'] ?? ''); ?>">
        </div>

        <script>
            (function() {
                const DOMAIN_LIMIT = <?php echo $plan['domain_limit']; ?>;
                const hiddenInput = document.getElementById('allowed-domains-hidden');
                const listContainer = document.getElementById('domain-list');
                const inputField = document.getElementById('new-domain-input');
                const addBtn = document.getElementById('add-domain-btn');

                let domains = hiddenInput.value ? hiddenInput.value.split(',').filter(d => d.trim() !== '') : [];

                window.renderDomains = function() {
                    listContainer.innerHTML = '';
                    domains.forEach((d, index) => {
                        const tag = document.createElement('span');
                        tag.style.cssText = 'background: #e9ecef; padding: 4px 8px; border-radius: 4px; display: inline-flex; align-items: center; font-size: 0.9rem; border: 1px solid #dee2e6;';
                        tag.innerHTML = `\${d} <i class="fa-solid fa-times" onclick="removeDomain(\${index})" style="margin-left: 6px; cursor: pointer; color: #666;"></i>`;
                        listContainer.appendChild(tag);
                    });

                    hiddenInput.value = domains.join(',');

                    // Enforce Limit
                    if (DOMAIN_LIMIT !== -1 && domains.length >= DOMAIN_LIMIT) {
                        inputField.disabled = true;
                        inputField.placeholder = 'Limit reached';
                        addBtn.disabled = true;
                        addBtn.style.opacity = '0.5';
                    } else {
                        inputField.disabled = false;
                        inputField.placeholder = 'example.com';
                        addBtn.disabled = false;
                        addBtn.style.opacity = '1';
                    }
                };

                window.addDomain = function() {
                    const val = inputField.value.trim().toLowerCase(); // Normalize
                    if (!val) return;

                    // Basic validation (simple)
                    if (domains.includes(val)) {
                        alert('Domain already added');
                        return;
                    }

                    if (DOMAIN_LIMIT !== -1 && domains.length >= DOMAIN_LIMIT) {
                        alert('Domain limit reached. Upgrade your plan.');
                        return;
                    }

                    domains.push(val);
                    inputField.value = '';
                    renderDomains();
                };

                window.removeDomain = function(index) {
                    domains.splice(index, 1);
                    renderDomains();
                };

                // Init
                renderDomains();
            })();
        </script>

        <div class="form-group" style="margin-top: 20px;">
            <label>Timezone</label>
            <select name="timezone" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                <?php
                $timezones = DateTimeZone::listIdentifiers();
                $currentTz = $widget['timezone'] ?? 'UTC';
                foreach ($timezones as $tz) {
                    $selected = ($tz == $currentTz) ? 'selected' : '';
                    echo "<option value=\"$tz\" $selected>$tz</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn">Save Settings</button>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
