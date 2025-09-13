<?php
require_once __DIR__ . '/../db/db.php';

class AdminFunctions {
    private $conn;
    public function __construct() {
        $db = new myDB();
        $this->conn = $db->conn;
    }

    public function searchPosts($search = '', $category = '', $author = '') {
        $sql = "SELECT p.*, u.email AS author 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                WHERE 1";
        $params = [];
        $types = "";

        if (!empty($search)) {
            $sql .= " AND (p.title LIKE ? OR p.subtitle LIKE ? OR p.content LIKE ?)";
            $types .= "sss";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($category)) {
            $sql .= " AND p.category = ?";
            $types .= "s";
            $params[] = $category;
        }

        if (!empty($author)) {
            $sql .= " AND u.email LIKE ?";
            $types .= "s";
            $params[] = "%$author%";
        }

        $sql .= " ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        if ($params)
            $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc())
            $rows[] = $r;
        return $rows;
    }

    public function getPostById($id) {
        $stmt = $this->conn->prepare("SELECT p.*, u.email AS author 
                                      FROM posts p 
                                      JOIN users u ON p.user_id=u.id 
                                      WHERE p.id=? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getCommentsByPostId($post_id) {
        $stmt = $this->conn->prepare("SELECT c.*, u.email AS author 
                                      FROM comments c 
                                      JOIN users u ON c.user_id=u.id 
                                      WHERE c.post_id=? 
                                      ORDER BY c.created_at ASC");
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc())
            $rows[] = $r;
        return $rows;
    }

    public function adminUpdatePost($post_id, $title, $subtitle, $content, $category, $image=null) {
        if ($image !== null) {
            $stmt = $this->conn->prepare("UPDATE posts SET title=?, subtitle=?, content=?, category=?, image=? WHERE id=?");
            $stmt->bind_param("sssssi", $title, $subtitle, $content, $category, $image, $post_id);
        } else {
            $stmt = $this->conn->prepare("UPDATE posts SET title=?, subtitle=?, content=?, category=? WHERE id=?");
            $stmt->bind_param("ssssi", $title, $subtitle, $content, $category, $post_id);
        }
        if ($stmt->execute())
            return "success";
        return "Failed to update post.";
    }

    public function adminDeletePost($post_id) {
        $stmt = $this->conn->prepare("DELETE FROM posts WHERE id=?");
        $stmt->bind_param("i", $post_id);
        if ($stmt->execute())
            return "success";
        return "Failed to delete post.";
    }
}
