<?php

require_once __DIR__ . "/../config/Database.php";

class User {
    private $conn;
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    public function login($username, $password) {
        /*
        |--------------------------------------------------------------------------
        | Defensive Programming
        |--------------------------------------------------------------------------
        */
        if (empty($username) || empty($password)) {
            return false;
        }
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}