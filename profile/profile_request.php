<?php
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../db/auth.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        echo "Unauthorized";
        exit;
    }

    $db = new myDB();
    $conn = $db->conn;

    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $stmt = $conn->prepare("SELECT email, password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($db_email, $db_password);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current_password, $db_password)) {
        echo "Current password is incorrect";
        exit;
    }

    if ($email !== $db_email) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo "Email already in use";
            exit;
        }
        $stmt->close();
    }

    if (!empty($new_password) || !empty($confirm_password)) {
        if ($new_password !== $confirm_password) {
            echo "New password and confirmation do not match";
            exit;
        }

        $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/";
        if (!preg_match($pattern, $new_password)) {
            echo "Password must be at least 8 characters and include upper, lower, number, and special char";
            exit;
        }

        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $email, $hashed, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->bind_param("si", $email, $user_id);
    }

    if ($stmt->execute()) {
        echo "Profile updated successfully";
    } else {
        echo "Error updating profile";
    }
    $stmt->close();
    exit;
}
?>
