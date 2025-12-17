<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div style="display: flex; gap: 20px; align-items: flex-start;">

    <!-- List Plans -->
    <div style="flex: 1;">
        <?php foreach ($plans as $plan):
            $feats = json_decode($plan['features'], true) ?? [];
        ?>
        <div class="card">
            <form action="/admin/plans/save" method="POST">
                <input type="hidden" name="id" value="<?php echo $plan['id']; ?>">

                <div style="display: flex; gap: 20px; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    <div style="flex: 1;">
                        <label>Plan Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($plan['name']); ?>" required>
                    </div>
                    <div style="width: 100px;">
                        <label>Price ($)</label>
                        <input type="number" name="monthly_price" step="0.01" value="<?php echo $plan['monthly_price']; ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>

                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <label>Monthly Visitor Limit (-1 for Unl.)</label>
                        <input type="number" name="visit_limit" value="<?php echo $plan['visit_limit']; ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div style="flex: 1;">
                        <label>Allowed Domains Limit (-1 for Unl.)</label>
                        <input type="number" name="domain_limit" value="<?php echo $plan['domain_limit']; ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>

                <label style="margin-bottom: 10px; display: block;">Enabled Features:</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <label class="toggle">
                        <input type="checkbox" name="feat_remove_branding" <?php echo !empty($feats['remove_branding']) ? 'checked' : ''; ?>>
                        Remove Branding
                    </label>
                    <label class="toggle">
                        <input type="checkbox" name="feat_coupons" <?php echo !empty($feats['coupons']) ? 'checked' : ''; ?>>
                        Coupons
                    </label>
                    <label class="toggle">
                        <input type="checkbox" name="feat_notifications" <?php echo !empty($feats['notifications']) ? 'checked' : ''; ?>>
                        Notifications
                    </label>
                    <label class="toggle">
                        <input type="checkbox" name="feat_videos" <?php echo !empty($feats['videos']) ? 'checked' : ''; ?>>
                        Videos
                    </label>
                    <label class="toggle">
                        <input type="checkbox" name="feat_newsletters" <?php echo !empty($feats['newsletters']) ? 'checked' : ''; ?>>
                        Newsletters
                    </label>
                    <label class="toggle">
                        <input type="checkbox" name="feat_socials" <?php echo !empty($feats['socials']) ? 'checked' : ''; ?>>
                        Socials
                    </label>
                    <label class="toggle">
                        <input type="checkbox" name="feat_reviews" <?php echo !empty($feats['reviews']) ? 'checked' : ''; ?>>
                        Reviews
                    </label>
                    <label class="toggle">
                        <input type="checkbox" name="feat_live_visitor" <?php echo !empty($feats['live_visitor']) ? 'checked' : ''; ?>>
                        Live Visitors
                    </label>
                    <label class="toggle">
                        <input type="checkbox" name="feat_live_conversion" <?php echo !empty($feats['live_conversion']) ? 'checked' : ''; ?>>
                        Live Conversion
                    </label>
                </div>

                <div style="margin-top: 20px; text-align: right;">
                    <button type="submit" class="btn">Save Changes</button>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Create New (Simple form) -->
    <div style="width: 300px;">
        <div class="card">
            <h2>Create New Plan</h2>
            <form action="/admin/plans/save" method="POST">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" placeholder="e.g. Enterprise" required>
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" name="monthly_price" step="0.01" value="0.00" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <label>MUV Limit</label>
                    <input type="number" name="visit_limit" value="1000" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <label>Domain Limit</label>
                    <input type="number" name="domain_limit" value="1" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <p class="text-muted" style="font-size: 0.8rem;">Features will default to disabled. Edit after creating.</p>

                <button type="submit" class="btn" style="width: 100%;">Create Plan</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
