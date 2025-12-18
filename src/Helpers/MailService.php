<?php

class MailService {
    private static $instance = null;
    private $pdo;
    private $apiKey;
    private $domain;
    private $fromEmail;

    private function __construct() {
        $this->pdo = Database::getInstance();
        $this->loadSettings();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadSettings() {
        // Fetch settings from system_settings table
        $stmt = $this->pdo->query("SELECT setting_key, setting_value FROM system_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $this->apiKey = $settings['mailgun_api_key'] ?? null;
        $this->domain = $settings['mailgun_domain'] ?? null;
        $this->fromEmail = $settings['mailgun_from_email'] ?? 'noreply@trustabee.io';
    }

    public function sendOTP($email, $otp) {
        $subject = "Your Login Verification Code";
        $text = "Your verification code is: {$otp}\n\nThis code will expire in 15 minutes.";

        return $this->sendEmail($email, $subject, $text);
    }

    public function sendPasswordReset($email, $token) {
        $link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/reset-password?token={$token}";
        $subject = "Reset Your Password";
        $text = "You requested a password reset.\n\nClick the link below to set a new password:\n{$link}\n\nThis link expires in 1 hour.";

        return $this->sendEmail($email, $subject, $text);
    }

    private function sendEmail($to, $subject, $text) {
        // Always log the email content for debugging/fallback
        // user requirement: "I need to know where the OTP is temporary stored so I can access to login as I dont have SMTP now"
        $logMessage = "--- EMAIL LOG ---\nTo: $to\nSubject: $subject\nBody: $text\n-----------------";
        error_log($logMessage);

        if (empty($this->apiKey) || empty($this->domain)) {
            // Mailgun not configured
            return false; // But we logged it, so it's "sent" to the log.
        }

        $url = "https://api.mailgun.net/v3/{$this->domain}/messages";
        $data = [
            'from' => $this->fromEmail,
            'to' => $to,
            'subject' => $subject,
            'text' => $text
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:' . $this->apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            return true;
        } else {
            error_log("Mailgun Error: " . $result);
            return false;
        }
    }
}
