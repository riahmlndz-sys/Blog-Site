<?php
session_start();

function require_login($redirect = "../login.php") {
    if (!isset($_SESSION['user_id'])) {
        header("Location: $redirect");
        exit;
    }
}

function require_role($role = 'admin', $redirect = "../index.php") {
    require_login();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: $redirect");
        exit;
    }
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_user_role() {
    return $_SESSION['role'] ?? null;
}
