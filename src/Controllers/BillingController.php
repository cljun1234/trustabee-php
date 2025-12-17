<?php

class BillingController {
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $plan = PlanManager::getUserPlan($userId);

        $pdo = Database::getInstance();
        $month = date('Y-m');

        // Calculate Usage (MUV & Impressions)
        $stmt = $pdo->prepare("
            SELECT SUM(muv_count) as total_muv, SUM(impression_count) as total_imp
            FROM widget_usage wu
            JOIN widgets w ON wu.widget_id = w.id
            WHERE w.user_id = ? AND wu.month_year = ?
        ");
        $stmt->execute([$userId, $month]);
        $usage = $stmt->fetch();

        $totalMuv = $usage['total_muv'] ?? 0;
        $totalImp = $usage['total_imp'] ?? 0;

        // Calculate Domain Usage
        // Count total entries in allowed_domains across all widgets
        $stmt = $pdo->prepare("SELECT allowed_domains FROM widgets WHERE user_id = ?");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        $domainCount = 0;
        foreach ($rows as $r) {
            if (!empty($r['allowed_domains'])) {
                $domains = array_filter(explode(',', $r['allowed_domains']), function($d) {
                    return !empty(trim($d));
                });
                $domainCount += count($domains);
            }
        }

        // Cycle Reset Date
        // Based on subscription_start.
        // If start = 2023-10-15, reset is 15th of current month.
        // If today < 15th, reset was last month 15th (cycle is 15th to 15th).
        // Wait, "Reset cycle are based on subscription date".
        // Let's just show "Next Billing Date".
        $subStart = $plan['subscription_start'] ?? date('Y-m-d');
        $startDay = date('d', strtotime($subStart));

        $currentMonth = date('m');
        $currentYear = date('Y');

        // Construct date for this month
        $resetDateThisMonth = date("$currentYear-$currentMonth-$startDay");

        if (strtotime($resetDateThisMonth) > time()) {
            $nextReset = $resetDateThisMonth;
        } else {
            $nextReset = date('Y-m-d', strtotime("+1 month", strtotime($resetDateThisMonth)));
        }

        $pageTitle = 'Billing & Plan';
        $activePage = 'billing';

        require_once __DIR__ . '/../../views/billing/index.php';
    }
}
