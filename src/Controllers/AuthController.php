<?php

require_once __DIR__ . '/../Helpers/MailService.php';

class AuthController {
    public function showLogin() {
        require_once __DIR__ . '/../../views/login.php';
    }

    public function processLogin() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Email and Password are required.";
            require_once __DIR__ . '/../../views/login.php';
            return;
        }

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Check verification and last login
            $verified = $user['verified'] ?? 0;
            $lastLogin = $user['last_login'] ? strtotime($user['last_login']) : 0;
            $oneWeekAgo = time() - (7 * 24 * 60 * 60);

            // Require OTP if not verified OR last login > 1 week ago
            if (!$verified || $lastLogin < $oneWeekAgo) {
                // Generate OTP
                $otp = rand(100000, 999999);
                $expiresAt = date('Y-m-d H:i:s', time() + 900); // 15 mins

                // Save OTP
                $stmtOtp = $pdo->prepare("INSERT INTO login_otps (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
                $stmtOtp->execute([$user['id'], $otp, $expiresAt]);

                // Send Email
                $mail = MailService::getInstance();
                $mail->sendOTP($user['email'], $otp);

                // Store user ID in session temporarily for verification
                $_SESSION['verify_user_id'] = $user['id'];

                header('Location: /verify-otp');
                exit;
            }

            // Success - Login
            $this->finalizeLogin($user);

        } else {
            $error = "Invalid credentials";
            require_once __DIR__ . '/../../views/login.php';
        }
    }

    private function finalizeLogin($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'] ?? 'user';

        // Update last login
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        header('Location: /');
        exit;
    }

    public function showRegister() {
        require_once __DIR__ . '/../../views/register.php';
    }

    public function processRegister() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Email and Password are required.";
            require_once __DIR__ . '/../../views/register.php';
            return;
        }

        $pdo = Database::getInstance();

        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered.";
            require_once __DIR__ . '/../../views/register.php';
            return;
        }

        // Create user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // Verified = 0 by default
        $stmt = $pdo->prepare("INSERT INTO users (email, password, verified) VALUES (?, ?, 0)");

        try {
            $stmt->execute([$email, $hashedPassword]);
            $user_id = $pdo->lastInsertId();

            // Create default widget
            $stmtWidget = $pdo->prepare("INSERT INTO widgets (user_id, domain, name) VALUES (?, ?, ?)");
            $stmtWidget->execute([$user_id, 'example.com', 'My First Widget']);

            // Send OTP for verification
            $otp = rand(100000, 999999);
            $expiresAt = date('Y-m-d H:i:s', time() + 900); // 15 mins

            $stmtOtp = $pdo->prepare("INSERT INTO login_otps (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
            $stmtOtp->execute([$user_id, $otp, $expiresAt]);

            $mail = MailService::getInstance();
            $mail->sendOTP($email, $otp);

            $_SESSION['verify_user_id'] = $user_id;
            header('Location: /verify-otp');
            exit;

        } catch (PDOException $e) {
            $error = "Registration failed. Please try again.";
            error_log("AuthController::processRegister Error: " . $e->getMessage());
            require_once __DIR__ . '/../../views/register.php';
        }
    }

    public function showVerifyOTP() {
        if (!isset($_SESSION['verify_user_id'])) {
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/../../views/verify_otp.php';
    }

    public function processVerifyOTP() {
        if (!isset($_SESSION['verify_user_id'])) {
            header('Location: /login');
            exit;
        }

        $otp = $_POST['otp'] ?? '';
        $user_id = $_SESSION['verify_user_id'];

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM login_otps WHERE user_id = ? AND otp_code = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$user_id, $otp]);
        $record = $stmt->fetch();

        if ($record) {
            // Success
            // Delete used OTPs
            $delParams = [$user_id];
            $delSql = "DELETE FROM login_otps WHERE user_id = ?";
            $stmtDel = $pdo->prepare($delSql);
            $stmtDel->execute($delParams);

            // Mark user verified
            $stmtUpd = $pdo->prepare("UPDATE users SET verified = 1 WHERE id = ?");
            $stmtUpd->execute([$user_id]);

            // Login
            $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmtUser->execute([$user_id]);
            $user = $stmtUser->fetch();

            unset($_SESSION['verify_user_id']);
            $this->finalizeLogin($user);
        } else {
            $error = "Invalid or expired OTP.";
            require_once __DIR__ . '/../../views/verify_otp.php';
        }
    }

    public function resendOTP() {
        if (!isset($_SESSION['verify_user_id'])) {
            header('Location: /login');
            exit;
        }
        $user_id = $_SESSION['verify_user_id'];
        $pdo = Database::getInstance();

        // Get email
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $email = $stmt->fetchColumn();

        if ($email) {
             // Generate OTP
             $otp = rand(100000, 999999);
             $expiresAt = date('Y-m-d H:i:s', time() + 900); // 15 mins

             $stmtOtp = $pdo->prepare("INSERT INTO login_otps (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
             $stmtOtp->execute([$user_id, $otp, $expiresAt]);

             $mail = MailService::getInstance();
             $mail->sendOTP($email, $otp);

             $error = "A new code has been sent."; // Re-using error variable for message in view
             require_once __DIR__ . '/../../views/verify_otp.php';
        } else {
            header('Location: /login');
        }
    }

    public function showForgotPassword() {
        require_once __DIR__ . '/../../views/forgot_password.php';
    }

    public function processForgotPassword() {
        $email = $_POST['email'] ?? '';

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate Token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            $stmtReset = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmtReset->execute([$email, $token, $expiresAt]);

            $mail = MailService::getInstance();
            $mail->sendPasswordReset($email, $token);
        }

        // Always show success message to prevent enumeration (or show real success if preferred, usually vague is safer)
        $success = "If an account exists with that email, a reset link has been sent.";
        require_once __DIR__ . '/../../views/forgot_password.php';
    }

    public function showResetPassword() {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            $error = "Invalid token.";
            require_once __DIR__ . '/../../views/reset_password.php';
            return;
        }
        // Verify token existence/expiry
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        if (!$stmt->fetch()) {
             $error = "This link is invalid or has expired.";
             require_once __DIR__ . '/../../views/reset_password.php';
             return;
        }

        require_once __DIR__ . '/../../views/reset_password.php';
    }

    public function processResetPassword() {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm) {
            $error = "Passwords do not match.";
            require_once __DIR__ . '/../../views/reset_password.php';
            return;
        }

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $resetRequest = $stmt->fetch();

        if ($resetRequest) {
            $email = $resetRequest['email'];
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Update Password
            $stmtUpdate = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmtUpdate->execute([$hashedPassword, $email]);

            // Delete Token
            $stmtDelete = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmtDelete->execute([$token]);

            // Redirect to login
            header('Location: /login');
            exit;
        } else {
            $error = "Invalid or expired token.";
            require_once __DIR__ . '/../../views/reset_password.php';
        }
    }

    public function logout() {
        session_destroy();
        header('Location: /login');
    }
}
