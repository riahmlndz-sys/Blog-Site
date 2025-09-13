<?php 
require_once __DIR__ . '/../../db/db.php';
require_once __DIR__ . '/../../db/auth.php';
require_role('admin');
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Users</title>
  <link rel="stylesheet" href="../../assets/css/main.css"> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="../../jquery.min.js"></script>
</head>
<body>

<div class="admin-layout">
  <aside class="sidebar">
    <nav class="menu">
      <a href="../admin.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
      <a href="manage_users.php" class="active"><i class="fa-solid fa-users"></i> Manage Users</a>
      <a href="../../profile/profile.php"><i class="fa-solid fa-user"></i> Profile</a>
      <a href="../../logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <h2>Manage Users</h2>
      <span>Welcome, <?= htmlspecialchars($_SESSION['email']) ?></span>
    </header>

    <main class="content-area">
      <div class="card">
        <h3 class="card-title">➕ Create New User</h3>
        <form id="addUserForm">
          <label>Email</label>
          <input type="email" name="email" class="form-control" required>
          
          <label>Role</label>
          <select name="role" class="form-control">
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>

          <input type="hidden" name="create_user" value="1">
          <button type="submit" class="btn-primary">Add User</button>
        </form>
      </div>

      <div class="card">
        <h3 class="card-title">👥 All Users</h3>
        <div id="userTable"></div>
      </div>
    </main>
  </div>
</div>

<div id="editWrap" class="modal">
  <div class="modal-content" style="max-width:500px;">
    <span class="modal-close">&times;</span>
    <h2>✏️ Edit User</h2>
    <div class="modal-body">
      <form id="editUserForm">
        <input type="hidden" name="id" id="edit_id">

        <div class="modal-section">
          <h3>📧 <span class="label">Email</span></h3>
          <input type="email" name="email" id="edit_email" class="form-control" required>
        </div>

        <div class="modal-section">
          <h3>👤 <span class="label">Role</span></h3>
          <select name="role" id="edit_role" class="form-control">
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <input type="hidden" name="update_user" value="1">

        <div class="modal-footer">
          <button type="submit" class="btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Save Changes
          </button>
          <button id="resetPasswordBtn" class="btn-danger">
            <i class="fa-solid fa-key"></i> Reset Password
          </button>
          <button type="button" id="editCancel" class="btn-accent">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
$(function(){
  function loadUsers(){
    $.post("manage_users_request.php", { get_users: 1 }, function(html){
      $("#userTable").html(html);
    });
  }
  loadUsers();

  $("#addUserForm").submit(function(e){
    e.preventDefault();
    $.post("manage_users_request.php", $(this).serialize(), function(res){
      alert(res); loadUsers();
    });
  });

  $("#userTable").on("click", ".edit-btn", function(){
    var id = $(this).data("id");
    $.post("manage_users_request.php", { get_single_user: 1, id: id }, function(res){
      try {
        var u = JSON.parse(res);
        $("#edit_id").val(u.id);
        $("#edit_email").val(u.email);
        $("#edit_role").val(u.role);
        $("#editWrap").fadeIn().css("display","flex");
      } catch(e){ alert("Failed to fetch user."); }
    });
  });

  $("#editCancel, #editWrap .modal-close").click(function(){ $("#editWrap").fadeOut(); });

  $("#editUserForm").submit(function(e){
    e.preventDefault();
    $.post("manage_users_request.php", $(this).serialize(), function(res){
      alert(res); $("#editWrap").fadeOut(); loadUsers();
    });
  });

  $("#resetPasswordBtn").click(function(){
    var id = $("#edit_id").val();
    if (!id) return;
    if (!confirm("Reset this user's password to Blogacc@1?")) return;
    $.post("manage_users_request.php", { reset_password: 1, id: id }, function(res){
      alert(res);
    });
  });

  $("#userTable").on("click", ".delete-btn", function(){
    var id = $(this).data("id");
    if (!confirm("Delete this user?")) return;
    $.post("manage_users_request.php", { delete_user: 1, id: id }, function(res){
      alert(res); loadUsers();
    });
  });
});
</script>
</body>
</html>
