<?php
require_once __DIR__ . '/../db/auth.php';
require_role('admin'); 
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="../jquery.min.js"></script>
</head>
<body>

<div class="admin-layout">
  <aside class="sidebar">
    <nav class="menu">
      <a href="#" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a>
      <a href="manage_users/manage_users.php"><i class="fa-solid fa-users"></i> Manage Users</a>
      <a href="../profile/profile.php"><i class="fa-solid fa-user"></i> Profile</a>
      <a href="../logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <h2>Admin Dashboard</h2>
      <span>Welcome, <?= htmlspecialchars($_SESSION['email']) ?></span>
    </header>

    <main class="content-area">
      <div class="card">
        <h2 class="card-title">Manage Posts</h2>
        <div class="flex gap-2">
          <input type="text" id="adminSearch" class="form-control" placeholder="🔍 Search by title or content">
          <select id="adminCategory" class="form-control">
              <option value="">All Categories</option>
              <option>Lifestyle</option>
              <option>Hobby and Interest</option>
              <option>Business and Professional</option>
              <option>News and Information</option>
          </select>
        </div>
      </div>

      <div id="adminPosts" class="card"></div>
    </main>
  </div>
</div>

<div id="adminViewModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>📄 View Post</h2>
    <div class="modal-body" id="adminViewContent"></div>
  </div>
</div>

<div id="adminEditWrap" class="modal">
  <div class="modal-content" style="max-width:650px;">
    <span class="modal-close">&times;</span>
    <h2>✏️ Edit Post</h2>
    <div class="modal-body">
      <form id="adminEditForm" enctype="multipart/form-data">

        <div class="modal-section">
          <h3>📝 <span class="label">Title</span></h3>
          <input name="title" id="admin_title" class="form-control">
        </div>

        <div class="modal-section">
          <h3>✨ <span class="label">Subtitle</span></h3>
          <input name="subtitle" id="admin_subtitle" class="form-control">
        </div>

        <div class="modal-section">
          <h3>📖 <span class="label">Content</span></h3>
          <textarea name="content" id="admin_content" class="form-control" rows="6"></textarea>
        </div>

        <div class="modal-section">
          <h3>📂 <span class="label">Category</span></h3>
          <select name="category" id="admin_category" class="form-control">
              <option>Lifestyle</option>
              <option>Hobby and Interest</option>
              <option>Business and Professional</option>
              <option>News and Information</option>
          </select>
        </div>

        <div class="modal-section">
          <h3>🖼️ <span class="label">Current Image</span></h3>
          <div id="admin_current_image"></div>
          <label>Change Image</label>
          <input type="file" name="image" class="form-control">
        </div>

        <input type="hidden" name="post_id" id="admin_post_id">
        <input type="hidden" name="admin_update_post" value="1">

        <div class="modal-footer">
          <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
          <button type="button" id="adminCancel" class="btn-accent">Cancel</button>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
$(function(){
    function loadAdmin(){
        $.post("admin_request.php", { 
            admin_get_posts: 1, 
            search: $("#adminSearch").val(), 
            category: $("#adminCategory").val()
        }, function(html){
            $("#adminPosts").html(html);
        });
    }
    loadAdmin();

    $("#adminSearch").on("keyup", loadAdmin);
    $("#adminCategory").on("change", loadAdmin);

    $("#adminPosts").on("click", ".admin-view", function(){
        var id = $(this).data("id");
        $.post("admin_request.php", { admin_view_post: 1, post_id: id }, function(res){
            $("#adminViewContent").html(res);
            $("#adminViewModal").fadeIn().css("display","flex");
        });
    });
    
    $("#adminViewModal .close").click(()=> $("#adminViewModal").fadeOut());

    $("#adminPosts").on("click", ".admin-edit", function(){
        var id = $(this).data("id");
        $.post("admin_request.php", { admin_get_single: 1, post_id: id }, function(res){
            try {
                var p = JSON.parse(res);
                $("#admin_post_id").val(p.id);
                $("#admin_title").val(p.title);
                $("#admin_subtitle").val(p.subtitle);
                $("#admin_content").val(p.content);
                $("#admin_category").val(p.category);
                if (p.image) {
                    $("#admin_current_image").html("<p>Current Image:<br><img src='../uploads/"+p.image+"' style='max-width:150px;'></p>");
                } else {
                    $("#admin_current_image").html("<p>No image uploaded.</p>");
                }
                $("#adminEditWrap").fadeIn().css("display","flex");
            } catch(e){ alert("Failed to fetch post."); }
        });
    });
    $("#adminCancel, #adminEditWrap .modal-close").click(()=> $("#adminEditWrap").fadeOut());

    $("#adminEditForm").submit(function(e){
        e.preventDefault();
        var fd = new FormData(this);
        $.ajax({
            url: "admin_request.php",
            method: "POST",
            data: fd,
            contentType: false,
            processData: false,
            success: function(res){
                alert(res);
                if (res.trim().toLowerCase()==='success') { $("#adminEditWrap").fadeOut(); loadAdmin(); }
            },
            error: function(){ alert("Something went wrong while updating post."); }
        });
    });

    $("#adminPosts").on("click", ".admin-delete", function(){
        var id = $(this).data("id");
        if (!confirm("Are you sure you want to delete this post?")) return;
        $.post("admin_request.php", { admin_delete_post: 1, post_id: id }, function(res){
            alert(res);
            loadAdmin();
        });
    });
});
</script>
</body>
</html>
