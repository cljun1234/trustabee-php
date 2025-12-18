<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div style="display: flex; gap: 20px; align-items: flex-start;">

    <!-- List Tokens -->
    <div style="flex: 1;">
        <?php foreach ($tokens as $token): ?>
        <div class="card" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <div>
                <h3><?php echo htmlspecialchars($token['name']); ?></h3>
                <code style="background: #eee; padding: 2px 5px; border-radius: 3px;"><?php echo htmlspecialchars($token['token']); ?></code>
                <p style="margin-top: 5px; color: #666; font-size: 0.9em;">
                    Allowed: <?php echo htmlspecialchars($token['allowed_domains'] ?: 'Any'); ?>
                </p>
            </div>
            <form action="/admin/tokens/delete" method="POST" onsubmit="return confirm('Delete this token?');">
                <input type="hidden" name="id" value="<?php echo $token['id']; ?>">
                <button type="submit" class="btn" style="background-color: #e74c3c;">Delete</button>
            </form>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
