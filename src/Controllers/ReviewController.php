<?php

class ReviewController {

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    public function index($widget_id = null) {
        $pdo = Database::getInstance();
        $user_id = $_SESSION['user_id'];

        if ($widget_id === null) {
            // Fetch widget for user
            $stmt = $pdo->prepare("SELECT id FROM widgets WHERE user_id = ? LIMIT 1");
            $stmt->execute([$user_id]);
            $widget = $stmt->fetch();
            if (!$widget) { die("No widget found."); }
            $widget_id = $widget['id'];
        } else {
            // Verify Widget Ownership
            $stmt = $pdo->prepare("SELECT id FROM widgets WHERE id = ? AND user_id = ?");
            $stmt->execute([$widget_id, $user_id]);
            if (!$stmt->fetch()) {
                die("Unauthorized");
            }
        }

        // Fetch Configuration
        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE widget_id = ?");
        $stmt->execute([$widget_id]);
        $review = $stmt->fetch();

        if (!$review) {
             // Create default
             $stmt = $pdo->prepare("INSERT INTO reviews (widget_id, active, show_reviews_widget) VALUES (?, 0, 0)");
             $stmt->execute([$widget_id]);
             $review_id = $pdo->lastInsertId();

             $stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ?");
             $stmt->execute([$review_id]);
             $review = $stmt->fetch();
        }

        // Fetch Review Items (Manual)
        $stmt = $pdo->prepare("SELECT * FROM review_items WHERE review_id = ? ORDER BY created_at DESC");
        $stmt->execute([$review['id']]);
        $items = $stmt->fetchAll();

        // Fetch Feedbacks
        $stmt = $pdo->prepare("SELECT * FROM review_feedbacks WHERE review_id = ? ORDER BY created_at DESC");
        $stmt->execute([$review['id']]);
        $feedbacks = $stmt->fetchAll();

        // Fetch Active Coupons (for dropdown)
        $stmt = $pdo->prepare("SELECT id, title, coupon_code FROM coupons WHERE widget_id = ? AND active = 1");
        $stmt->execute([$widget_id]);
        $coupons = $stmt->fetchAll();

        require_once __DIR__ . '/../../views/campaigns/reviews.php';
    }

    // Save Configuration
    public function save() {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $widget_id = $input['widget_id'] ?? 0;

            if (!$this->isWidgetOwner($widget_id)) {
                throw new Exception("Unauthorized");
            }

            $pdo = Database::getInstance();

            // Check if exists
            $stmt = $pdo->prepare("SELECT id FROM reviews WHERE widget_id = ?");
            $stmt->execute([$widget_id]);
            $exists = $stmt->fetchColumn();

            $active = !empty($input['active']) ? 1 : 0;
            $show_widget = !empty($input['show_reviews_widget']) ? 1 : 0;
            $remove_branding = !empty($input['remove_branding']) ? 1 : 0;

            if ($exists) {
                $sql = "UPDATE reviews SET
                    popup_title = ?, popup_description = ?,
                    google_review_link = ?, facebook_review_link = ?,
                    high_star_action = ?, high_star_message = ?, high_star_coupon_id = ?, high_star_redirect_url = ?,
                    low_star_action = ?, low_star_message = ?, low_star_coupon_id = ?, low_star_redirect_url = ?,
                    trigger_type = ?, trigger_delay = ?, frequency = ?, match_url = ?,
                    show_reviews_widget = ?, widget_position = ?,
                    remove_branding = ?, active = ?
                    WHERE widget_id = ?";
                $params = [
                    $input['popup_title'], $input['popup_description'],
                    $input['google_review_link'], $input['facebook_review_link'],
                    $input['high_star_action'], $input['high_star_message'], $input['high_star_coupon_id'] ?: null, $input['high_star_redirect_url'],
                    $input['low_star_action'], $input['low_star_message'], $input['low_star_coupon_id'] ?: null, $input['low_star_redirect_url'],
                    $input['trigger_type'], $input['trigger_delay'], $input['frequency'], $input['match_url'],
                    $show_widget, $input['widget_position'],
                    $remove_branding, $active,
                    $widget_id
                ];
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            } else {
                $sql = "INSERT INTO reviews (
                    widget_id, popup_title, popup_description, google_review_link, facebook_review_link,
                    high_star_action, high_star_message, high_star_coupon_id, high_star_redirect_url,
                    low_star_action, low_star_message, low_star_coupon_id, low_star_redirect_url,
                    trigger_type, trigger_delay, frequency, match_url,
                    show_reviews_widget, widget_position, remove_branding, active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [
                    $widget_id,
                    $input['popup_title'], $input['popup_description'],
                    $input['google_review_link'], $input['facebook_review_link'],
                    $input['high_star_action'], $input['high_star_message'], $input['high_star_coupon_id'] ?: null, $input['high_star_redirect_url'],
                    $input['low_star_action'], $input['low_star_message'], $input['low_star_coupon_id'] ?: null, $input['low_star_redirect_url'],
                    $input['trigger_type'], $input['trigger_delay'], $input['frequency'], $input['match_url'],
                    $show_widget, $input['widget_position'],
                    $remove_branding, $active
                ];
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }

            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Save Review Item
    public function saveItem() {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $widget_id = $input['widget_id'] ?? 0;

            if (!$this->isWidgetOwner($widget_id)) {
                throw new Exception("Unauthorized");
            }

            $pdo = Database::getInstance();

            // Get Review Config ID
            $stmt = $pdo->prepare("SELECT id FROM reviews WHERE widget_id = ?");
            $stmt->execute([$widget_id]);
            $review_id = $stmt->fetchColumn();

            if (!$review_id) {
                 // Create default if not exists
                 $stmt = $pdo->prepare("INSERT INTO reviews (widget_id) VALUES (?)");
                 $stmt->execute([$widget_id]);
                 $review_id = $pdo->lastInsertId();
            }

            $sql = "INSERT INTO review_items (review_id, name, review_text, rating, image_url, source, source_link) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $review_id,
                $input['name'],
                $input['review_text'],
                (int)$input['rating'],
                $input['image_url'],
                $input['source'],
                $input['source_link']
            ]);

            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function deleteItem($id) {
        header('Content-Type: application/json');

        try {
            // Verify ownership via join
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare("
                SELECT ri.id FROM review_items ri
                JOIN reviews r ON ri.review_id = r.id
                JOIN widgets w ON r.widget_id = w.id
                WHERE ri.id = ? AND w.user_id = ?
            ");
            $stmt->execute([$id, $_SESSION['user_id']]);

            if (!$stmt->fetch()) {
                 throw new Exception("Unauthorized");
            }

            $stmt = $pdo->prepare("DELETE FROM review_items WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true]);

        } catch (Exception $e) {
             echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Helper
    private function isWidgetOwner($widget_id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT id FROM widgets WHERE id = ? AND user_id = ?");
        $stmt->execute([$widget_id, $_SESSION['user_id']]);
        return (bool)$stmt->fetch();
    }
}
