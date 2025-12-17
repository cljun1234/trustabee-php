<?php

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
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            header('Location: /');
            exit;
        } else {
            $error = "Invalid credentials";
            require_once __DIR__ . '/../../views/login.php';
        }
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
        $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");

        try {
            $stmt->execute([$email, $hashedPassword]);
            $user_id = $pdo->lastInsertId();

            // Create default widget
            $stmtWidget = $pdo->prepare("INSERT INTO widgets (user_id, domain, name) VALUES (?, ?, ?)");
            $stmtWidget->execute([$user_id, 'example.com', 'My First Widget']);

            // Auto login
            $_SESSION['user_id'] = $user_id;
            header('Location: /');
            exit;

        } catch (PDOException $e) {
            $error = "Registration failed. Please try again.";
            // Log error with context for production debugging
            error_log("AuthController::processRegister Error: " . $e->getMessage());
            require_once __DIR__ . '/../../views/register.php';
        }
    }

    public function logout() {
        session_destroy();
        header('Location: /login');
    }
}
