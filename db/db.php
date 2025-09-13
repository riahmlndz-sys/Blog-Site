<?php
class myDB {
    private $servername = 'localhost';
    private $username = 'root';    
    private $password = '';
    private $db_name = 'blog_db';
    public $conn;

    public function __construct() {
        try {
            $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->db_name); 
            if ($this->conn->connect_error) {
                die("Database connection failed: " . $this->conn->connect_error);
            }
        } catch(Exception $e) {
            die("Database connection error!<br>".$e);
        }
    }
}
?>
