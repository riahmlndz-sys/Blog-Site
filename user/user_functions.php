<?php
require_once __DIR__ . '/../db/db.php';

class UserFunctions {
    private $conn;
    public function __construct() {
        $db = new myDB();
        $this->conn = $db->conn;
    }

    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $r = $stmt->get_result();
        return $r->num_rows > 0;
    }

    public function registerUser($email, $password) {
        if ($this->emailExists($email)) {
            return "⚠ Email already registered.";
        }

        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/', $password)) {
            return "⚠ Password must be at least 8 characters and include letters and numbers.";
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (email, password, role, created_at) VALUES (?, ?, 'user', NOW())");
        $stmt->bind_param("ss", $email, $hash);
        if ($stmt->execute())
            return "success";
        return "Database error: Failed to register.";
    }

    public function loginUser($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, email, password, role FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $r = $stmt->get_result();
        if ($r && $user = $r->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                return $user;
            } else {
                return "Invalid credentials.";
            }
        }
        return "Invalid credentials.";
    }

    public function createPost($user_id, $title, $subtitle, $content, $category, $image_filename = null) {
        $stmt = $this->conn->prepare("INSERT INTO posts (user_id, title, subtitle, content, image, category, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssss", $user_id, $title, $subtitle, $content, $image_filename, $category);
        if ($stmt->execute())
            return "success";
        return "Failed to create post.";
    }

    public function updatePost($post_id, $user_id, $title, $subtitle, $content, $category, $image_filename = null) {
        if ($image_filename !== null) {
            $stmt = $this->conn->prepare("UPDATE posts SET title=?, subtitle=?, content=?, category=?, image=? WHERE id=? AND user_id=?");
            $stmt->bind_param("sssssii", $title, $subtitle, $content, $category, $image_filename, $post_id, $user_id);
        } else {
            $stmt = $this->conn->prepare("UPDATE posts SET title=?, subtitle=?, content=?, category=? WHERE id=? AND user_id=?");
            $stmt->bind_param("ssssii", $title, $subtitle, $content, $category, $post_id, $user_id);
        }

        if ($stmt->execute())
            return "success";
        return "Failed to update post or you are not the owner.";
    }

    public function deletePost($post_id, $user_id) {
        $stmt = $this->conn->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $post_id, $user_id);
        if ($stmt->execute())
            return "success";
        return "Failed to delete post or you are not the owner.";
    }

    public function getPosts($mode = 'mine', $user_id = null, $search = '', $category = '') {
        $sql = "SELECT p.*, u.email AS author FROM posts p JOIN users u ON p.user_id = u.id WHERE 1";
        $params = [];
        $types = "";

        if ($mode === 'mine' && $user_id) {
            $sql .= " AND p.user_id = ?";
            $types .= "i";
            $params[] = $user_id;
        } elseif ($mode === 'others' && $user_id) {
            $sql .= " AND p.user_id != ?";
            $types .= "i";
            $params[] = $user_id;
        }

        if (!empty($search)) {
            $sql .= " AND (p.title LIKE ? OR p.content LIKE ?)";
            $types .= "ss";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($category)) {
            $sql .= " AND p.category = ?";
            $types .= "s";
            $params[] = $category;
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc())
            $rows[] = $r;
        return $rows;
    }

    public function toggleLike($post_id, $user_id) {
        $stmt = $this->conn->prepare("SELECT id FROM likes WHERE post_id=? AND user_id=?");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
        $r = $stmt->get_result();
        if ($r->num_rows > 0) {
            $stmt = $this->conn->prepare("DELETE FROM likes WHERE post_id=? AND user_id=?");
            $stmt->bind_param("ii", $post_id, $user_id);
            $stmt->execute();
        } else {
            $stmt = $this->conn->prepare("INSERT INTO likes (post_id, user_id, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ii", $post_id, $user_id);
            $stmt->execute();
        }
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS cnt FROM likes WHERE post_id=?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $cnt = $stmt->get_result()->fetch_assoc()['cnt'];
        return (string)$cnt;
    }

    public function addComment($post_id, $user_id, $comment) {
        $stmt = $this->conn->prepare("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $post_id, $user_id, $comment);
        if ($stmt->execute())
            return "success";
        return "Failed to add comment.";
    }

    public function getCommentsHtml($post_id) {
        $stmt = $this->conn->prepare("SELECT c.*, u.email FROM comments c JOIN users u ON c.user_id=u.id WHERE c.post_id=? ORDER BY c.created_at ASC");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $html = '';
        while ($r = $res->fetch_assoc()) {
            $email = htmlspecialchars($r['email']);
            $text = nl2br(htmlspecialchars($r['comment']));
            $time = $r['created_at'];
            $html .= "<p><strong>{$email}</strong> <small>{$time}</small><br>{$text}</p>";
        }
        if ($html === '')
            $html = "<p>No comments yet.</p>";
        return $html;
    }

    public function getLikesCount($post_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS cnt FROM likes WHERE post_id=?");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        return (string)$stmt->get_result()->fetch_assoc()['cnt'];
    }

    public function getPostById($post_id) {
        $stmt = $this->conn->prepare("SELECT p.*, u.email AS author FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id=? LIMIT 1");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public function getCommentsByPostId($post_id) {
        $stmt = $this->conn->prepare("SELECT c.*, u.email AS author FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
