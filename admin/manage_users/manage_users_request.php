<?php
require_once __DIR__ . '/../../db/db.php';
require_once __DIR__ . '/../../db/auth.php';
require_role('admin');

$db = new myDB();
$conn = $db->conn;

if (isset($_POST['get_users'])) {
    $res = $conn->query("SELECT id, email, role, created_at FROM users ORDER BY created_at DESC");
    echo "<table class='table'><thead>
            <tr><th>#</th><th>Email</th><th>Role</th><th>Created At</th><th>Actions</th></tr>
          </thead><tbody>";
    $i=1;
    while ($r = $res->fetch_assoc()) {
        $id = $r['id'];
        $email = htmlspecialchars($r['email']);
        $role = htmlspecialchars($r['role']);
        $created = $r['created_at'];
        echo "<tr>
                <td>$i</td>
                <td>$email</td>
                <td>$role</td>
                <td>$created</td>
                <td>
                  <button class='btn-primary edit-btn' data-id='$id'><i class='fa-solid fa-pen'></i> Edit</button>
                  <button class='btn-danger delete-btn' data-id='$id'><i class='fa-solid fa-trash'></i> Delete </button>
                </td>
              </tr>";
        $i++;
    }
    echo "</tbody></table>";
    exit;
}

if (isset($_POST['get_single_user'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("SELECT id, email, role FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();
    echo json_encode($u);
    exit;
}

if (isset($_POST['create_user'])) {
    $email = trim($_POST['email']);
    $role = $_POST['role'] ?? 'user';

    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo "Email already exists."; exit;
    }

    $defaultPass = password_hash("Blogacc@1", PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (email, password, role, created_at) VALUES (?,?,?,NOW())");
    $stmt->bind_param("sss", $email, $defaultPass, $role);
    if ($stmt->execute()) echo "User created successfully.";
    else echo "Failed to create user.";
    exit;
}

if (isset($_POST['update_user'])) {
    $id = intval($_POST['id']);
    $email = trim($_POST['email']);
    $role = $_POST['role'] ?? 'user';

    $stmt = $conn->prepare("SELECT id FROM users WHERE email=? AND id!=?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo "Email already in use."; exit;
    }

    $stmt = $conn->prepare("UPDATE users SET email=?, role=? WHERE id=?");
    $stmt->bind_param("ssi", $email, $role, $id);
    if ($stmt->execute()) echo "User updated successfully.";
    else echo "Failed to update user.";
    exit;
}

if (isset($_POST['reset_password'])) {
    $id = intval($_POST['id']);
    $defaultPass = password_hash("Blogacc@1", PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $defaultPass, $id);
    if ($stmt->execute()) echo "Password reset to Blogacc@1";
    else echo "Failed to reset password.";
    exit;
}

if (isset($_POST['delete_user'])) {
    $id = intval($_POST['id']);
    if ($id === $_SESSION['user_id']) {
        echo "You cannot delete your own account.";
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) echo "User deleted successfully.";
    else echo "Failed to delete user.";
    exit;
}