<?php
require_once __DIR__ . '/admin_functions.php';
require_once __DIR__ . '/../db/auth.php';
require_role('admin');

$af = new AdminFunctions();

if (isset($_POST['admin_get_posts'])) {
    $search = $_POST['search'] ?? '';
    $category = $_POST['category'] ?? '';
    $rows = $af->searchPosts($search, $category);

    if (empty($rows)) {
        echo "<p>No posts found.</p>";
        exit;
    }

    $html = "<table class='table'>
                <thead>
                    <tr>
                        <th>#</th><th>Title</th><th>Subtitle</th><th>Category</th><th>Actions</th>
                    </tr>
                </thead><tbody>";
    $i=1;
    foreach ($rows as $r) {
        $id = $r['id'];
        $title = htmlspecialchars($r['title']);
        $subtitle = htmlspecialchars($r['subtitle']);
        $cat = htmlspecialchars($r['category']);

        $titlePreview = strlen($title) > 40 ? substr($title, 0, 40) . "…" : $title;
        $subtitlePreview = strlen($subtitle) > 50 ? substr($subtitle, 0, 50) . "…" : $subtitle;

        $html .= "<tr>
                    <td>{$i}</td>
                    <td title='{$title}'>{$titlePreview}</td>
                    <td title='{$subtitle}'>{$subtitlePreview}</td>
                    <td>{$cat}</td>
                    <td>
                        <button class='btn-primary admin-view' data-id='{$id}'><i class='fa-solid fa-eye'></i> View</button>
                        <button class='btn-primary admin-edit' data-id='{$id}'><i class='fa-solid fa-pen'></i> Edit</button>
                        <button class='btn-danger admin-delete' data-id='{$id}'><i class='fa-solid fa-trash'></i> Delete</button>
                    </td>
                  </tr>";
        $i++;
    }
    $html .= "</tbody></table>";
    echo $html;
    exit;
}

if (isset($_POST['admin_view_post'])) {
    $id = intval($_POST['post_id']);
    $p = $af->getPostById($id);
    if (!$p) {
        echo "Post not found.";
        exit;
    }

    $comments = $af->getCommentsByPostId($id);

    echo "
    <div class='modal-section'>
      <h3>📝 <span class='label'>Title:</span></h3>
      <p class='text-bold'>".htmlspecialchars($p['title'])."</p>
    </div>

    <div class='modal-section'>
      <h3>✨ <span class='label'>Subtitle:</span></h3>
      <p class='text-italic'>".htmlspecialchars($p['subtitle'])."</p>
    </div>

    <div class='modal-section'>
      <h3>📖 <span class='label'>Content:</span></h3>
      <p class='text-justify'>".nl2br(htmlspecialchars($p['content']))."</p>
    </div>";

    if ($p['image']) {
        echo "
        <div class='modal-section'>
          <h3>🖼️ <span class='label'>Image:</span></h3>
          <div class='image-preview'>
            <img src='../uploads/".htmlspecialchars($p['image'])."' alt='Post image'>
          </div>
        </div>";
    }

    echo "
    <div class='modal-section'>
      <h3>👤 <span class='label'>Author & Date:</span></h3>
      <p><b>".htmlspecialchars($p['author'])."</b><br><small>{$p['created_at']}</small></p>
    </div>

    <div class='modal-section'>
      <h3>💬 <span class='label'>Comments:</span></h3>";
      
    if (empty($comments)) {
        echo "<p>No comments yet.</p>";
    } else {
        echo "<blockquote>";
        foreach ($comments as $c) {
            $commentText = $c['comment'] ?? '';
            echo "<p><b>".htmlspecialchars($c['author'])."</b>: ".htmlspecialchars($commentText)."</p>";
        }
        echo "</blockquote>";
    }

    echo "</div>";
    exit;
}

if (isset($_POST['admin_get_single'])) {
    $id = intval($_POST['post_id'] ?? 0);
    $p = $af->getPostById($id);
    if (!$p)
        echo "Post not found.";
    else {
        echo json_encode($p);
    }
    exit;
}

if (isset($_POST['admin_update_post'])) {
    $post_id = intval($_POST['post_id']);
    $title = trim($_POST['title']);
    $subtitle = trim($_POST['subtitle']);
    $content = trim($_POST['content']);
    $category = $_POST['category'] ?? 'Lifestyle';
    $image_name = null;

    if (!empty($_FILES['image']['name'])) {
        $targetDir = __DIR__ . '/../uploads/';
        if (!is_dir($targetDir))
            mkdir($targetDir, 0777, true);
        $image_name = time() . "_" . basename($_FILES['image']['name']);
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $image_name)) {
            echo "Failed to upload image.";
            exit;
        }
    }

    echo $af->adminUpdatePost($post_id,$title,$subtitle,$content,$category,$image_name);
    exit;
}

if (isset($_POST['admin_delete_post'])) {
    $post_id = intval($_POST['post_id'] ?? 0);
    echo $af->adminDeletePost($post_id);
    exit;
}
