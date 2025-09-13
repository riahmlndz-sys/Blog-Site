<?php
require_once __DIR__ . '/../db/auth.php';
require_login();
if ($_SESSION['role'] === 'admin') {
    header("Location: ../admin/admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="../jquery.min.js"></script>
</head>
<body>

<div class="admin-layout">
    <aside class="sidebar">
      <nav class="menu">
        <a href="user.php" id="tab-mine" class="active"><i class="fa-solid fa-user-pen"></i> My Posts</a>
        <a href="#" id="tab-others"><i class="fa-solid fa-globe"></i> From Other Authors</a>
        <a href="../profile/profile.php"><i class="fa-solid fa-user"></i> Profile</a>
        <a href="../logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      </nav>
    </aside>

  <div class="main-content">
    <header class="topbar">
      <h2>User Dashboard</h2>
      <span>Welcome, <?=htmlspecialchars($_SESSION['email'])?></span>
    </header>

    <main class="content-area">
      <div class="card">
        <h2 class="card-title">Browse Posts</h2>
        <div class="flex gap-2">
          <input type="text" id="searchBox" class="form-control" placeholder="🔍 Search posts...">
          <select id="categoryFilter" class="form-control">
              <option value="">All Categories</option>
              <option>Lifestyle</option>
              <option>Hobby and Interest</option>
              <option>Business and Professional</option>
              <option>News and Information</option>
          </select>
          <button id="openCreate" class="btn-primary"><i class="fa-solid fa-plus"></i> Create Post</button>
        </div>
      </div>

      <div id="postsContainer" class="card"></div>
    </main>
  </div>
</div>

<div id="postFormWrap" class="modal">
  <div class="modal-content">
    <span class="modal-close">&times;</span>
    <h2 id="formTitle">Create Post</h2>
    <form id="postForm" enctype="multipart/form-data">
      <input type="hidden" name="post_id" id="post_id">

      <div class="modal-section">
        <h3>📝 Title</h3>
        <input type="text" name="title" id="title" class="form-control" required>
      </div>

      <div class="modal-section">
        <h3>✨ Subtitle</h3>
        <input type="text" name="subtitle" id="subtitle" class="form-control">
      </div>

      <div class="modal-section">
        <h3>📖 Content</h3>
        <textarea name="content" id="content" class="form-control" required></textarea>
      </div>

      <div class="modal-section">
        <h3>📂 Category</h3>
        <select name="category" id="category" class="form-control">
          <option>Lifestyle</option>
          <option>Hobby and Interest</option>
          <option>Business and Professional</option>
          <option>News and Information</option>
        </select>
      </div>

      <div class="modal-section">
        <h3>🖼️ Image</h3>
        <input type="file" name="image" id="image" class="form-control">
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save</button>
        <button type="button" id="cancelPost" class="btn-accent" style="color: #fff;">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
$(document).ready(function () {
    var mode = 'mine';
    function loadPosts(search) {
        var data = {
            get_posts: 1,
            mode: mode,
            search: search || $("#searchBox").val(),
            category: $("#categoryFilter").val()
        };

        $.post("user_request.php", data, function(result){
            $("#postsContainer").html(result);
        }).fail(function(){
            alert("Something went wrong while loading posts.");
        });
    }

    loadPosts('');

    $("#searchBox").on("keyup", function (){
        loadPosts($(this).val());
    });

    $("#categoryFilter").on("change", function(){
        loadPosts('');
    });
    
    $("#tab-mine").click(function(e){ 
        e.preventDefault();
        mode = 'mine'; 
        $(".menu a").removeClass("active");
        $(this).addClass("active");     
        loadPosts('');
    });

    $("#tab-others").click(function(e){ 
        e.preventDefault();
        mode = 'others'; 
        $(".menu a").removeClass("active");
        $(this).addClass("active");
        loadPosts('');
    });

    $("#openCreate").click(function(){
        $("#formTitle").text("Create Post");
        $("#postForm")[0].reset();
        $("#post_id").val('');
        $("#postFormWrap").css("display","flex");
    });

    $("#cancelPost, .modal-close").click(function(){
        $("#postFormWrap").hide();
    });

    $("#postForm").on("submit", function(e){
        e.preventDefault();
        var fd = new FormData(this);
        var isUpdate = $("#post_id").val() ? true : false;
        if (isUpdate)
            fd.append("update_post", "1");
        else fd.append("create_post", "1");

        $.ajax({
            url: "user_request.php",
            method: "POST",
            data: fd,
            contentType: false,
            processData: false,
            success: function(res){
                alert(res);
                if (res.trim().toLowerCase() === "success") {
                    $("#postFormWrap").hide();
                    loadPosts('');
                }
            },
            error: function(){ alert("Error saving post."); }
        });
    });

    $("#postsContainer").on("click", ".delete-link", function(e){
        e.preventDefault();
        if (!confirm("Delete this post?"))
            return;
        var id = $(this).data("id");
        $.post("user_request.php", {
            delete_post: 1,
            post_id: id
        }, function(res){
            alert(res);
            if (res.trim().toLowerCase() === "success")
                loadPosts('');
        });
    });

    $("#postsContainer").on("click", ".edit-link", function(e){
        e.preventDefault();
        var id = $(this).data("id");

        $.post("user_request.php", {
            get_post: 1,
            post_id: id
        }, function(res){
            try {
                var post = JSON.parse(res);

                $("#formTitle").text("Edit Post");
                $("#post_id").val(post.id);
                $("#title").val(post.title);
                $("#subtitle").val(post.subtitle);
                $("#content").val(post.content);
                $("#category").val(post.category);

                if (post.image) {
                    $("#postFormWrap").find(".current-image").remove();
                    $("#image").before(
                        "<div class='current-image'>Current: <img src='../uploads/"+post.image+"' style='max-width:100px;'><br></div>"
                    );
                }

                $("#postFormWrap").css("display","flex");
                $(".modal-content").css("animation","slideDown 0.3s ease-out"); 
            } catch(e) {
                alert("Failed to load post details.");
            }
        });
    });

    $("#postsContainer").on("click", ".like-btn", function(){
        var btn = $(this), postId = btn.data("id");
        $.post("user_request.php", {
            toggle_like: 1,
            post_id: postId
        }, function(res){
            btn.find(".like-count").text(res);
            btn.toggleClass("liked");
        });
    });

});
</script>
</body>
</html>
