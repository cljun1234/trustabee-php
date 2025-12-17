<?php

class SocialController {

    private const PLATFORMS_CONFIG = [
        'facebook'  => ['icon' => 'fa-facebook-f', 'color' => '#1877f2'],
        'twitter'   => ['icon' => 'fa-twitter',    'color' => '#1da1f2'],
        'instagram' => ['icon' => 'fa-instagram',  'color' => '#c32aa3'],
        'linkedin'  => ['icon' => 'fa-linkedin-in','color' => '#0a66c2'],
        'youtube'   => ['icon' => 'fa-youtube',    'color' => '#ff0000'],
        'tiktok'    => ['icon' => 'fa-tiktok',     'color' => '#000000'],
        'pinterest' => ['icon' => 'fa-pinterest-p','color' => '#bd081c'],
        'whatsapp'  => ['icon' => 'fa-whatsapp',   'color' => '#25d366'],
        'telegram'  => ['icon' => 'fa-telegram',   'color' => '#0088cc'],
        'discord'   => ['icon' => 'fa-discord',    'color' => '#5865F2'],
        'reddit'    => ['icon' => 'fa-reddit-alien','color' => '#FF4500'],
        'snapchat'  => ['icon' => 'fa-snapchat',   'color' => '#FFFC00'],
        'spotify'   => ['icon' => 'fa-spotify',    'color' => '#1DB954'],
    ];

    private function getWidgetAndUser() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $pdo = Database::getInstance();
        $user_id = $_SESSION['user_id'];

        // Fetch widget
        $stmt = $pdo->prepare("SELECT * FROM widgets WHERE user_id = ? LIMIT 1");
        $stmt->execute([$user_id]);
        $widget = $stmt->fetch();

        if (!$widget) {
            echo "No widget found.";
            exit;
        }

        return [$pdo, $user_id, $widget];
    }

    public function index($widget_id) {
        $pdo = Database::getInstance();

        // Ensure widget belongs to user
        if (!isset($_SESSION['user_id'])) { header('Location: /login'); exit; }
        $user_id = $_SESSION['user_id'];

        // Fetch specific social widget config
        $stmt = $pdo->prepare("SELECT * FROM socials WHERE widget_id = ?");
        $stmt->execute([$widget_id]);
        $social = $stmt->fetch();

        if (!$social) {
            // Create default if not exists
            $stmt = $pdo->prepare("INSERT INTO socials (widget_id, active, position) VALUES (?, 0, 'bottom-right')");
            $stmt->execute([$widget_id]);
            $social_id = $pdo->lastInsertId();

            // Re-fetch
            $stmt = $pdo->prepare("SELECT * FROM socials WHERE id = ?");
            $stmt->execute([$social_id]);
            $social = $stmt->fetch();
        }

        // Fetch links
        $stmt = $pdo->prepare("SELECT * FROM social_links WHERE social_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$social['id']]);
        $links = $stmt->fetchAll();

        // Prepare links map for easy access in view
        $platformsConfig = self::PLATFORMS_CONFIG;
        // Keep $platforms for backward compatibility if needed by view (it loops over keys)
        $platforms = array_keys($platformsConfig);

        $links_map = [];
        foreach ($links as $link) {
            $links_map[$link['platform']] = $link;
        }

        require_once __DIR__ . '/../../views/campaigns/social.php';
    }

    public function save() {
        list($pdo, $user_id, $widget) = $this->getWidgetAndUser();

        $social_id = $_POST['social_id'] ?? 0;

        // Verify ownership
        $stmt = $pdo->prepare("SELECT id FROM socials WHERE id = ? AND widget_id = ?");
        $stmt->execute([$social_id, $widget['id']]);
        if (!$stmt->fetch()) { die("Unauthorized"); }

        $title = $_POST['title'] ?? 'Follow Us';
        $subtitle = $_POST['subtitle'] ?? '';
        $remove_branding = isset($_POST['remove_branding']) ? 1 : 0;
        $position = $_POST['position'] ?? 'bottom-right';

        // Triggers
        $trigger_type = $_POST['trigger_type'] ?? 'delay';
        $trigger_delay = (int)($_POST['trigger_delay'] ?? 0);
        $frequency = $_POST['frequency'] ?? 'session';

        $stmt = $pdo->prepare("UPDATE socials SET title = ?, subtitle = ?, remove_branding = ?, position = ?, trigger_type = ?, trigger_delay = ?, frequency = ? WHERE id = ?");
        $stmt->execute([$title, $subtitle, $remove_branding, $position, $trigger_type, $trigger_delay, $frequency, $social_id]);

        // Save Links
        $platforms = array_keys(self::PLATFORMS_CONFIG);

        // First reset all to inactive
        $stmt = $pdo->prepare("UPDATE social_links SET is_active = 0 WHERE social_id = ?");
        $stmt->execute([$social_id]);

        foreach ($platforms as $platform) {
            $active = isset($_POST['platform_' . $platform . '_active']) ? 1 : 0;
            $url = $_POST['platform_' . $platform . '_url'] ?? '';
            $label = $_POST['platform_' . $platform . '_label'] ?? '';

            if ($active) {
                // Check if exists
                $stmt = $pdo->prepare("SELECT id FROM social_links WHERE social_id = ? AND platform = ?");
                $stmt->execute([$social_id, $platform]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $stmt = $pdo->prepare("UPDATE social_links SET url = ?, label_text = ?, is_active = 1 WHERE id = ?");
                    $stmt->execute([$url, $label, $existing['id']]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO social_links (social_id, platform, url, label_text, is_active) VALUES (?, ?, ?, ?, 1)");
                    $stmt->execute([$social_id, $platform, $url, $label]);
                }
            }
        }

        header('Location: /campaigns/social');
    }

    public function toggle() {
        header('Content-Type: application/json');
        list($pdo, $user_id, $widget) = $this->getWidgetAndUser();

        $input = json_decode(file_get_contents('php://input'), true);
        $enabled = !empty($input['enabled']);
        $social_id = $input['id'] ?? 0;

        // Verify ownership
        $stmt = $pdo->prepare("SELECT id FROM socials WHERE id = ? AND widget_id = ?");
        $stmt->execute([$social_id, $widget['id']]);
        if (!$stmt->fetch()) {
             echo json_encode(['error' => 'Unauthorized']); exit;
        }

        $stmt = $pdo->prepare("UPDATE socials SET active = ? WHERE id = ?");
        $stmt->execute([$enabled ? 1 : 0, $social_id]);

        echo json_encode(['success' => true]);
    }
}
