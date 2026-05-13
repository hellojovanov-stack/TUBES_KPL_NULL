<?php

require_once "Database.php";

class User {

    private $conn;

    public function __construct() {

        $database = new Database();

        $this->conn = $database->connect();
    }

    public function login($username,$password) {

        if(empty($username) || empty($password)) {
            return false;
        }

        $query = "SELECT * FROM users
        WHERE username=:username
        AND password=MD5(:password)";

        $stmt = $this->conn->prepare($query);

        $stmt->execute([
            ':username'=>$username,
            ':password'=>$password
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}