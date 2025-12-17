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
        <div class="form-group toggle">
            <label style="display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" name="magical_detection" value="1" <?php if($widget['magical_detection']) echo 'checked'; ?> style="width: auto; margin-right: 10px;">
                Enable "Magical Detection" (Auto-capture form submissions)
            </label>
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <label>Allowed Domains (Comma separated)</label>
            <p style="font-size: 0.8rem; color: #666; margin-top: 0;">
                Limit: <?php echo ($plan['domain_limit'] == -1) ? 'Unlimited' : $plan['domain_limit']; ?> domains.
                Only these domains are allowed to load the widget.
            </p>
            <input type="text" name="allowed_domains" value="<?php echo htmlspecialchars($widget['allowed_domains'] ?? ''); ?>" placeholder="example.com, blog.example.com">
        </div>

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
