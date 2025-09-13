<?php
require_once __DIR__ . '/../db/auth.php';
require_once __DIR__ . '/user_functions.php';
require_login();

$uf = new UserFunctions();

$post_id = intval($_GET['id'] ?? 0);
$post = $uf->getPostById($post_id);
if (!$post) { echo "⚠ Post not found."; exit; }

$comments = $uf->getCommentsByPostId($post_id);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?=htmlspecialchars($post['title'])?></title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="../jquery.min.js"></script>
</head>
<body>
<div class="admin-layout">
    <div class="main-content">
        <header class="topbar">
            <h2>📄 View Post</h2>
            <span style="margin-bottom:15px;"><a href="user.php" class="btn-cancel"><i class="fa-solid fa-arrow-left"></i> Back</a></span>
        </header>

        <main class="content-area">
            <div class="card modal-content" style="max-width:900px; margin:auto;">
                <h2 class="card-title">📝 <?=htmlspecialchars($post['title'])?></h2>
                <h3 class="card-subtitle">✨ <?=htmlspecialchars($post['subtitle'])?></h3>
                <small>👤 <b><?=htmlspecialchars($post['author'])?></b> | <?=htmlspecialchars($post['created_at'])?></small>

                <?php if ($post['image']): ?>
                <div class="image-preview">
                    <img src="../uploads/<?=htmlspecialchars($post['image'])?>" alt="Post image">
                </div>
                <?php endif; ?>

                <p class="text-justify" style="margin-top:1rem;"><?=nl2br(htmlspecialchars($post['content']))?></p>

                <div class="card-actions" style="justify-content:flex-start; margin-top:15px;">
                    <button class="like-btn" data-id="<?=$post['id']?>">
                        <i class="fa-solid fa-heart"></i> <span class="like-count"><?=$uf->getLikesCount($post_id)?></span>
                    </button>
                </div>

                <hr style="margin:25px 0;">
                <h3>💬 Comments</h3>
                <div id="comments">
                    <?php if(empty($comments)): ?>
                        <p>No comments yet.</p>
                    <?php else: ?>
                        <blockquote>
                        <?php foreach($comments as $c): ?>
                            <p>
                                <b><?=htmlspecialchars($c['author'] ?? $c['email'])?></b> 
                                <small><?=htmlspecialchars($c['created_at'])?></small><br>
                                <?=nl2br(htmlspecialchars($c['comment']))?>
                            </p>
                        <?php endforeach; ?>
                        </blockquote>
                    <?php endif; ?>
                </div>

                <form id="commentForm" class="mt-2">
                    <input type="hidden" name="post_id" value="<?=$post_id?>">
                    <textarea name="comment" class="form-control" placeholder="Add a comment..." required></textarea>
                    <div class="card-actions" style="margin-top:10px;">
                        <button type="submit" class="btn-primary"><i class="fa-solid fa-paper-plane"></i> Add Comment</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<script>
$(function(){
    const POST_ID = <?=json_encode($post_id)?>;

    $(".like-btn").click(function(){
        const btn = $(this);
        $.post("user_request.php", { toggle_like: 1, post_id: POST_ID }, function(res){
            btn.find(".like-count").text(res);
        });
    });

    $("#commentForm").on("submit", function(e){
        e.preventDefault();
        const formData = $(this).serialize() + "&add_comment=1";
        $.post("user_request.php", formData, function(res){
            if(res.trim() === "success"){
                $.post("user_request.php", { get_comments: 1, post_id: POST_ID }, function(html){
                    $("#comments").html(html);
                });
                $("#commentForm textarea").val("");
            } else {
                alert(res);
            }
        });
    });
});
</script>
</body>
</html>
