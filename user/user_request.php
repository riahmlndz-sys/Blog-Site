<?php
require_once __DIR__ . '/user_functions.php';
require_once __DIR__ . '/../db/auth.php';

$uf = new UserFunctions();

if (isset($_POST['register_user'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $res = $uf->registerUser($email, $password);
    echo $res;
    exit;
}

if (isset($_POST['login_user'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $login = $uf->loginUser($email, $password);
    if (is_array($login)) {
        $_SESSION['user_id'] = $login['id'];
        $_SESSION['email'] = $login['email'];
        $_SESSION['role'] = $login['role'];
        echo "success";
    } else {
        echo $login;
    }
    exit;
}

if (isset($_POST['create_post'])) {
    require_login();
    $user_id = current_user_id();
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? 'Lifestyle';

    $image_name = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = __DIR__ . '/../uploads/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $image_name = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $image_name;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            echo "Failed to upload image.";
            exit;
        }
    }

    echo $uf->createPost($user_id, $title, $subtitle, $content, $category, $image_name);
    exit;
}

if (isset($_POST['update_post'])) {
    require_login();
    $user_id = current_user_id();
    $post_id = intval($_POST['post_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = $_POST['category'] ?? 'Lifestyle';

    $image_name = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = __DIR__ . '/../uploads/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $image_name = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $image_name;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            echo "Failed to upload image.";
            exit;
        }
    }

    echo $uf->updatePost($post_id, $user_id, $title, $subtitle, $content, $category, $image_name);
    exit;
}

if (isset($_POST['delete_post'])) {
    require_login();
    $user_id = current_user_id();
    $post_id = intval($_POST['post_id'] ?? 0);
    echo $uf->deletePost($post_id, $user_id);
    exit;
}

if (isset($_POST['get_posts'])) {
    require_login();
    $mode = $_POST['mode'] ?? 'mine'; 
    $search = trim($_POST['search'] ?? '');
    $category = $_POST['category'] ?? '';
    $user_id = current_user_id();
    $rows = $uf->getPosts($mode, $user_id, $search, $category);

    $html = '';
    if (empty($rows)) {
        $html = "<p>No posts found.</p>";
    } else {
        foreach ($rows as $r) {
            $id = $r['id'];
            $title = htmlspecialchars($r['title']);
            $subtitle = htmlspecialchars($r['subtitle']);
            $author = htmlspecialchars($r['author']);
            $created = $r['created_at'];
            $category = htmlspecialchars($r['category']);
            $image = $r['image'] ? "../uploads/".htmlspecialchars($r['image']) : "";
            $likeCount = $uf->getLikesCount($id);

            $html .= "<div class='card post' data-post-id='{$id}' data-category='{$category}'>";
            
            if ($image) {
                $html .= "<img src='{$image}' alt='Post image'>";
            }

            $html .= "<div class='card-body'>
                        <h3 class='card-title'>{$title}</h3>
                        <h4 class='card-subtitle'>{$subtitle}</h4>
                        <small class='text-muted'>By {$author} | {$created}</small>
                        <p>".nl2br(htmlspecialchars(substr($r['content'],0,150)))."...</p>
                      </div>
                      <div class='card-actions'>
                        <a href='post.php?id={$id}' class='btn-view'>See More</a>
                        <button class='like-btn' data-id='{$id}'>❤️ <span class='like-count'>{$likeCount}</span></button>";

            if ($r['user_id'] == $user_id) {
                $html .= "<a href='#' class='edit-link' data-id='{$id}'><i class='fa-solid fa-pen'></i>Edit</a>
                          <a href='#' class='delete-link' data-id='{$id}'><i class='fa-solid fa-trash'></i>Delete</a>";
            }

            $html .= "</div></div>";
        }
    }
    echo $html;
    exit;
}

if (isset($_POST['get_comments'])) {
    $post_id = intval($_POST['post_id'] ?? 0);
    echo $uf->getCommentsHtml($post_id);
    exit;
}

if (isset($_POST['toggle_like'])) {
    require_login();
    $user_id = current_user_id();
    $post_id = intval($_POST['post_id'] ?? 0);
    echo $uf->toggleLike($post_id, $user_id);
    exit;
}

if (isset($_POST['add_comment'])) {
    require_login();
    $user_id = current_user_id();
    $post_id = intval($_POST['post_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    echo $uf->addComment($post_id, $user_id, $comment);
    exit;
}

if (isset($_POST['get_post'])) {
    require_login();
    $post_id = intval($_POST['post_id'] ?? 0);
    $user_id = current_user_id();
    $post = $uf->getPostById($post_id);

    if (!$post || $post['user_id'] != $user_id) {
        echo "not_found";
        exit;
    }
    echo json_encode($post);
    exit;
}
