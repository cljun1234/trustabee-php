<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div style="display: flex; gap: 20px; align-items: flex-start;">

    <!-- List Tokens -->
    <div style="flex: 1;">
        <?php foreach ($tokens as $token):
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
            $magicLink = $baseUrl . "/partner-auth?token=" . $token['token'] . "&source=partner&email={USER_EMAIL}";
        ?>
        <div class="card" style="margin-bottom: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 style="margin: 0 0 5px 0;"><?php echo htmlspecialchars($token['name']); ?></h3>
                    <p style="margin: 0 0 10px 0; color: #666; font-size: 0.9em;">
                        Allowed: <?php echo htmlspecialchars($token['allowed_domains'] ?: 'Any'); ?>
                    </p>
                </div>
                <form action="/admin/tokens/delete" method="POST" onsubmit="return confirm('Delete this token?');">
                    <input type="hidden" name="id" value="<?php echo $token['id']; ?>">
                    <button type="submit" class="btn-sm">Delete</button>
                </form>
            </div>

            <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #eee; margin-top: 10px;">
                <label style="font-size: 0.8rem; color: #999; display: block; margin-bottom: 5px;">Magic Link Template (Replace {USER_EMAIL})</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" readonly value="<?php echo htmlspecialchars($magicLink); ?>" id="token-<?php echo $token['id']; ?>" style="flex: 1; font-family: monospace; font-size: 0.85rem; padding: 5px; background: white;">
                    <button type="button" class="btn" style="padding: 5px 10px; font-size: 0.8rem;" onclick="copyToClipboard('token-<?php echo $token['id']; ?>')">Copy</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if(empty($tokens)): ?>
            <p>No partner tokens created yet.</p>
        <?php endif; ?>
    </div>

    <!-- Create New -->
    <div style="width: 300px;">
        <div class="card">
            <h2>Create Token</h2>
            <form action="/admin/tokens/save" method="POST">
                <div class="form-group">
                    <label>Partner Name</label>
                    <input type="text" name="name" placeholder="e.g. Hostinger" required>
                </div>
                <div class="form-group">
                    <label>Allowed Domains</label>
                    <textarea name="allowed_domains" placeholder="example.com, mysite.org" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                    <small style="color: #666;">Comma separated. Leave empty for any.</small>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Generate Token</button>
            </form>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    var copyText = document.getElementById(elementId);
    copyText.select();
    copyText.setSelectionRange(0, 99999); /* For mobile devices */
    navigator.clipboard.writeText(copyText.value).then(function() {
        alert("Copied link to clipboard!");
    }, function(err) {
        alert("Could not copy text: ", err);
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
