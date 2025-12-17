<?php

class PlanManager {

    // Cache the plan for the request
    private static $userPlan = null;

    /**
     * Get the current user's plan limits and features.
     * Uses the user_id associated with the widget or the authenticated user.
     *
     * @param int $userId
     * @return array
     */
    public static function getUserPlan($userId) {
        if (self::$userPlan && self::$userPlan['user_id'] == $userId) {
            return self::$userPlan;
        }

        $pdo = Database::getInstance();

        // Fetch User's Plan
        $stmt = $pdo->prepare("
            SELECT u.plan_id, p.*
            FROM users u
            JOIN plans p ON u.plan_id = p.id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $plan = $stmt->fetch();

        // Default to Free Plan (ID 1) if not found
        if (!$plan) {
            $stmt = $pdo->prepare("SELECT * FROM plans WHERE id = 1");
            $stmt->execute();
            $plan = $stmt->fetch();
        }

        // Decode features JSON
        $plan['features'] = json_decode($plan['features'], true) ?? [];
        $plan['user_id'] = $userId;

        self::$userPlan = $plan;
        return $plan;
    }

    public static function checkFeature($userId, $featureKey) {
        $plan = self::getUserPlan($userId);
        return isset($plan['features'][$featureKey]) && $plan['features'][$featureKey] === true;
    }

    public static function checkDomainLimit($userId, $currentDomainCount) {
        $plan = self::getUserPlan($userId);
        if ($plan['domain_limit'] == -1) return true;
        return $currentDomainCount < $plan['domain_limit'];
    }

    // Check if the specific domain is allowed for the widget
    public static function isDomainAllowed($widgetId, $originDomain) {
        if (empty($originDomain)) return false; // Block if no origin (strict mode) - or maybe allow for testing?

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT allowed_domains FROM widgets WHERE id = ?");
        $stmt->execute([$widgetId]);
        $allowed = $stmt->fetchColumn();

        if (empty($allowed)) {
            // If no allowed domains set, what is the default behavior?
            // "only that domain can run the script".
            // So if list is empty, NO domain can run it? Or maybe the domain created with the widget?
            // Let's assume if empty, it's strictly blocked, OR we fallback to the 'domain' column in widgets table?
            // User requirement: "they can add specific domain... that only that domain can run the script"
            // Let's check the 'domain' column in widgets table too.
            $stmt = $pdo->prepare("SELECT domain FROM widgets WHERE id = ?");
            $stmt->execute([$widgetId]);
            $mainDomain = $stmt->fetchColumn();

            // Normalize
            $allowedList = [$mainDomain];
        } else {
            $allowedList = array_map('trim', explode(',', $allowed));
            // Add the main domain too? Usually yes.
            $stmt = $pdo->prepare("SELECT domain FROM widgets WHERE id = ?");
            $stmt->execute([$widgetId]);
            $mainDomain = $stmt->fetchColumn();
            $allowedList[] = $mainDomain;
        }

        // Parse origin to get host
        $host = parse_url($originDomain, PHP_URL_HOST);
        if (!$host) $host = $originDomain; // fallback

        foreach ($allowedList as $d) {
            if (empty($d)) continue;
            // Check endsWith for subdomains or exact match?
            // "example.com" usually implies "www.example.com" too.
            // Let's do a loose match: if host ends with domain.
            if (strpos($host, $d) !== false) return true;
        }

        return false;
    }
}
