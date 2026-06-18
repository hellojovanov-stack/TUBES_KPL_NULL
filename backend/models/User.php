<?php

require_once "Database.php";

class User {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE username=:username";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check if password matches (user schema says Bcrypt hash, so we should use password_verify)
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }
}
?>