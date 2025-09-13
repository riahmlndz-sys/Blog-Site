<?php
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../db/auth.php';

$role = $_SESSION['role'] ?? '';
$dashboard = ($role === 'admin') ? '../admin/admin.php' : '../user/user.php';

$currentEmail = "";
if (isset($_SESSION['user_id'])) {
    $db = new myDB();
    $conn = $db->conn;
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($currentEmail);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Profile</title>
  <link rel="stylesheet" href="../assets/css/main.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="../jquery.min.js"></script>
</head>
<body>

<div class="admin-layout">
  <aside class="sidebar">
    <nav class="menu">
      <?php if ($role === 'admin'): ?>
        <a href="../admin/admin.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="../admin/manage_users/manage_users.php"><i class="fa-solid fa-users"></i> Manage Users</a>
        <a href="profile.php" class="active"><i class="fa-solid fa-user"></i> Profile</a>
        <a href="../logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      <?php else: ?>
        <a href="../user/user.php" id="tab-mine"><i class="fa-solid fa-user-pen"></i> My Posts</a>
        <a href="../user/user.php#others" id="tab-others"><i class="fa-solid fa-globe"></i> From Other Authors</a>
        <a href="profile.php" class="active"><i class="fa-solid fa-user"></i> Profile</a>
        <a href="../logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      <?php endif; ?>
    </nav>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <h2>Edit Profile</h2>
      <span>Welcome, <?= htmlspecialchars($_SESSION['email']) ?></span>
    </header>

    <main class="content-area">
      <div class="card">
        <h2 class="card-title">Edit Profile</h2>

        <form id="profileForm">
          <div class="modal-section">
            <h3>📧 <span class="label">Email</span></h3>
            <input type="email" name="email" class="form-control" 
                   value="<?= htmlspecialchars($currentEmail); ?>" required>
          </div>

          <div class="modal-section">
            <h3>🔑 <span class="label">Current Password</span></h3>
            <input type="password" name="current_password" class="form-control" required>
          </div>

          <div class="modal-section">
            <h3>✨ <span class="label">New Password</span></h3>
            <input type="password" name="new_password" class="form-control">
          </div>

          <div class="modal-section">
            <h3>🔁 <span class="label">Confirm New Password</span></h3>
            <input type="password" name="confirm_password" class="form-control">
          </div>

          <input type="hidden" name="update_profile" value="1">

          <div class="modal-footer">
            <button type="submit" class="btn-primary">
              <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
            <a href="<?= $dashboard ?>" class="btn-cancel" style="color: #fff;">Cancel</a>
          </div>
        </form>
      </div>
    </main>
  </div>
</div>

<script>
  $(function(){
    $("#profileForm").submit(function(e){
      e.preventDefault();
      $.post("profile_request.php", $(this).serialize(), function(res){
        alert(res);
        if (res.toLowerCase().includes("success")) {
          location.reload();
        }
      });
    });
  });
</script>
</body>
</html>
