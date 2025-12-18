<?php

class AdminController {

    private function checkAdmin() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Ensure role is up to date (or if session expired/missing role)
        if (!isset($_SESSION['role'])) {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $_SESSION['role'] = $stmt->fetchColumn() ?: 'user';
        }

        if ($_SESSION['role'] !== 'owner') {
            header('Location: /');
            exit;
        }
    }

    public function plans() {
        $this->checkAdmin();
        $pdo = Database::getInstance();
        $stmt = $pdo->query("SELECT * FROM plans ORDER BY monthly_price ASC");
        $plans = $stmt->fetchAll();

        // Pass data to view
        $pageTitle = 'Manage Plans';
        $activePage = 'admin';
        $activeSubPage = 'plans';

        require_once __DIR__ . '/../../views/admin/plans.php';
    }

    public function savePlan() {
        $this->checkAdmin();

        $id = $_POST['id'] ?? null;
        $name = $_POST['name'];
        $display_name = $_POST['display_name'] ?? null;
        $price = $_POST['monthly_price'];
        $visit_limit = $_POST['visit_limit'];
        $domain_limit = $_POST['domain_limit'];

        // Features
        $features = [
            'remove_branding' => isset($_POST['feat_remove_branding']),
            'coupons' => isset($_POST['feat_coupons']),
            'notifications' => isset($_POST['feat_notifications']),
            'videos' => isset($_POST['feat_videos']),
            'newsletters' => isset($_POST['feat_newsletters']),
            'socials' => isset($_POST['feat_socials']),
            'reviews' => isset($_POST['feat_reviews']),
            'live_visitor' => isset($_POST['feat_live_visitor']),
            'live_conversion' => isset($_POST['feat_live_conversion']),
        ];

        $featuresJson = json_encode($features);

        $pdo = Database::getInstance();

        if ($id) {
            $stmt = $pdo->prepare("UPDATE plans SET name=?, display_name=?, monthly_price=?, visit_limit=?, domain_limit=?, features=? WHERE id=?");
            $stmt->execute([$name, $display_name, $price, $visit_limit, $domain_limit, $featuresJson, $id]);
        } else {
            // Create new
            $stmt = $pdo->prepare("INSERT INTO plans (name, display_name, monthly_price, visit_limit, domain_limit, features) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $display_name, $price, $visit_limit, $domain_limit, $featuresJson]);
        }

        header('Location: /admin/plans');
    }

    public function tokens() {
        $this->checkAdmin();
        $pdo = Database::getInstance();
        $stmt = $pdo->query("SELECT * FROM partner_tokens ORDER BY created_at DESC");
        $tokens = $stmt->fetchAll();

        $pageTitle = 'Partner Tokens';
        $activePage = 'admin';
        $activeSubPage = 'tokens';

        require_once __DIR__ . '/../../views/admin/tokens.php';
    }

    public function saveToken() {
        $this->checkAdmin();
        $pdo = Database::getInstance();

        $name = $_POST['name'];
        $allowed_domains = $_POST['allowed_domains'];

        // Generate a token if not provided (though we are creating new ones mostly)
        // If it's a new token
        $token = bin2hex(random_bytes(16)); // 32 chars

        $stmt = $pdo->prepare("INSERT INTO partner_tokens (name, token, allowed_domains) VALUES (?, ?, ?)");
        $stmt->execute([$name, $token, $allowed_domains]);

        header('Location: /admin/tokens');
    }

    public function deleteToken() {
        $this->checkAdmin();
        $pdo = Database::getInstance();
        $id = $_POST['id'];

        $stmt = $pdo->prepare("DELETE FROM partner_tokens WHERE id = ?");
        $stmt->execute([$id]);

        header('Location: /admin/tokens');
    }

    public function users() {
        $this->checkAdmin();
        $pdo = Database::getInstance();

        // Get Users with Plan Name
        $stmt = $pdo->query("SELECT u.*, p.name as plan_name FROM users u LEFT JOIN plans p ON u.plan_id = p.id ORDER BY u.created_at DESC");
        $users = $stmt->fetchAll();

        // Get Plans for dropdown
        $stmt = $pdo->query("SELECT id, name FROM plans ORDER BY monthly_price ASC");
        $allPlans = $stmt->fetchAll();

        $pageTitle = 'Manage Users';
        $activePage = 'admin';
        $activeSubPage = 'users';

        require_once __DIR__ . '/../../views/admin/users.php';
    }

    public function updateUserPlan() {
        $this->checkAdmin();

        $user_id = $_POST['user_id'];
        $plan_id = $_POST['plan_id'];

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE users SET plan_id = ? WHERE id = ?");
        $stmt->execute([$plan_id, $user_id]);

        header('Location: /admin/users');
    }

    public function toggleUserRole() {
        $this->checkAdmin();

        $user_id = $_POST['user_id'];
        $role = $_POST['role']; // 'user' or 'owner'

        // Prevent removing own admin access if only one admin?
        // For simplicity, just update.

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $user_id]);

        header('Location: /admin/users');
    }

    public function emailSettings() {
        $this->checkAdmin();
        $pdo = Database::getInstance();

        // Fetch current settings
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $pageTitle = 'Manage Email';
        $activePage = 'admin';
        $activeSubPage = 'email';

        require_once __DIR__ . '/../../views/admin/email_settings.php';
    }

    public function saveEmailSettings() {
        $this->checkAdmin();
        $pdo = Database::getInstance();

        $keys = ['mailgun_api_key', 'mailgun_domain', 'mailgun_from_email'];

        foreach ($keys as $key) {
            $value = $_POST[$key] ?? '';
            // Insert or Update
            $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }

        header('Location: /admin/email');
    }
}
