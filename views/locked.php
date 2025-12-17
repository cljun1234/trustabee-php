<?php require_once __DIR__ . '/layouts/header.php'; ?>

<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 60vh; text-align: center;">
    <div style="background: #fff; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); max-width: 500px; width: 90%;">
        <div style="background: #f8f9fa; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
            <i class="fa-solid fa-lock" style="font-size: 32px; color: #6c757d;"></i>
        </div>

        <h2 style="font-size: 24px; margin-bottom: 10px; border: none;">Feature Locked</h2>
        <p style="color: #666; margin-bottom: 30px; line-height: 1.6;">
            This feature is not available in your current plan (<strong><?php echo htmlspecialchars($plan['name'] ?? 'Current'); ?></strong>).
            Upgrade your subscription to unlock it.
        </p>

        <a href="#" class="btn" style="padding: 12px 30px; font-size: 16px; background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%); border: none;">
            <i class="fa-solid fa-rocket" style="margin-right: 8px;"></i> Upgrade Plan
        </a>

        <p style="margin-top: 20px; font-size: 0.85rem;">
            <a href="/billing" style="color: #999; text-decoration: none;">View Plans</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
