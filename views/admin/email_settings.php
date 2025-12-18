<?php
// views/admin/email_settings.php
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="card">
    <h2>Mailgun Configuration</h2>
    <p class="text-muted">Configure your Mailgun API credentials here. These are used for sending OTPs and password reset emails.</p>

    <form action="/admin/email/save" method="POST">
        <div class="form-group">
            <label for="mailgun_api_key">Mailgun API Key</label>
            <input type="text" id="mailgun_api_key" name="mailgun_api_key" value="<?php echo htmlspecialchars($settings['mailgun_api_key'] ?? ''); ?>" placeholder="key-xxxxxxxxxxxxxxxx">
        </div>

        <div class="form-group">
            <label for="mailgun_domain">Mailgun Domain</label>
            <input type="text" id="mailgun_domain" name="mailgun_domain" value="<?php echo htmlspecialchars($settings['mailgun_domain'] ?? ''); ?>" placeholder="mg.yourdomain.com">
        </div>

        <div class="form-group">
            <label for="mailgun_from_email">From Email Address</label>
            <input type="text" id="mailgun_from_email" name="mailgun_from_email" value="<?php echo htmlspecialchars($settings['mailgun_from_email'] ?? ''); ?>" placeholder="noreply@yourdomain.com">
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn">Save Settings</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
