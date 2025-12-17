<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div style="max-width: 800px; margin: 0 auto;">

    <!-- Plan Overview -->
    <div class="card" style="text-align: center; border-top: 4px solid var(--primary-color);">
        <h2 style="border: none;">Current Plan: <span style="color: var(--primary-color); font-size: 1.5rem;"><?php echo htmlspecialchars($plan['name']); ?></span></h2>
        <p style="font-size: 1.2rem; font-weight: bold;">$<?php echo $plan['monthly_price']; ?> <span style="font-size: 0.9rem; font-weight: normal;">/ month</span></p>
        <p class="text-muted">Next billing cycle: <strong><?php echo date('F d, Y', strtotime($nextReset)); ?></strong></p>
    </div>

    <!-- Usage Stats -->
    <div class="card">
        <h3>Plan Usage</h3>

        <!-- MUV -->
        <div style="margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <label>Monthly Unique Visitors</label>
                <span style="font-weight: bold;">
                    <?php echo number_format($totalMuv); ?> /
                    <?php echo ($plan['visit_limit'] == -1) ? 'Unlimited' : number_format($plan['visit_limit']); ?>
                </span>
            </div>
            <?php
                $muvPercent = ($plan['visit_limit'] > 0) ? min(100, ($totalMuv / $plan['visit_limit']) * 100) : 0;
                $barColor = ($muvPercent > 90) ? '#dc3545' : '#1a73e8';
                if ($plan['visit_limit'] == -1) $muvPercent = 100; // Full bar but blue for unlimited? Or minimal? Let's hide bar for unlimited or show full blue.
                if ($plan['visit_limit'] == -1) $barColor = '#28a745';
            ?>
            <div style="background: #eee; height: 10px; border-radius: 5px; overflow: hidden;">
                <div style="width: <?php echo $muvPercent; ?>%; background: <?php echo $barColor; ?>; height: 100%;"></div>
            </div>
        </div>

        <!-- Domains -->
        <div style="margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <label>Active Domains</label>
                <span style="font-weight: bold;">
                    <?php echo $domainCount; ?> /
                    <?php echo ($plan['domain_limit'] == -1) ? 'Unlimited' : $plan['domain_limit']; ?>
                </span>
            </div>
            <?php
                $domPercent = ($plan['domain_limit'] > 0) ? min(100, ($domainCount / $plan['domain_limit']) * 100) : 0;
                $barColor = ($domPercent >= 100) ? '#dc3545' : '#1a73e8';
                if ($plan['domain_limit'] == -1) { $domPercent = 100; $barColor = '#28a745'; }
            ?>
            <div style="background: #eee; height: 10px; border-radius: 5px; overflow: hidden;">
                <div style="width: <?php echo $domPercent; ?>%; background: <?php echo $barColor; ?>; height: 100%;"></div>
            </div>
        </div>

        <!-- Impressions (Info Only) -->
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 24px; color: #28a745;"><i class="fa-solid fa-eye"></i></div>
            <div>
                <div style="font-size: 0.9rem; color: #666;">Total Impressions (Unlimited)</div>
                <div style="font-size: 1.2rem; font-weight: bold;"><?php echo number_format($totalImp); ?></div>
            </div>
        </div>

    </div>

    <!-- Feature List -->
    <div class="card">
        <h3>Included Features</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <?php
            $allFeats = [
                'remove_branding' => 'Remove Branding',
                'coupons' => 'Coupons',
                'notifications' => 'Notifications',
                'videos' => 'Videos',
                'newsletters' => 'Newsletters',
                'socials' => 'Social Widgets',
                'reviews' => 'Reviews',
                'live_visitor' => 'Live Visitors',
                'live_conversion' => 'Live Conversion'
            ];
            foreach($allFeats as $key => $label):
                $enabled = !empty($plan['features'][$key]);
            ?>
            <div style="display: flex; align-items: center; gap: 10px; opacity: <?php echo $enabled ? 1 : 0.5; ?>;">
                <i class="fa-solid <?php echo $enabled ? 'fa-check-circle' : 'fa-lock'; ?>" style="color: <?php echo $enabled ? 'green' : '#999'; ?>;"></i>
                <span><?php echo $label; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
