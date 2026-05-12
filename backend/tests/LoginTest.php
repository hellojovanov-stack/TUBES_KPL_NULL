<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/../config/Database.php";

class UserTest {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    public function login($username, $password) {
        // Defensive Programming: Cek input kosong
        if (empty($username) || empty($password)) {
            return false;
        }
        try {
            $query = "SELECT * FROM users WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            echo "<br><span style='color:red;'>[Database Error]: " . $e->getMessage() . "</span><br>";
            return false;
        }
    }
}

echo "<pre>";
echo "Menjalankan Unit Testing untuk Fitur Login...\n";
echo "------------------------------------------------\n";

$userTester = new UserTest();

echo "Test 1: Input Kosong -> ";
echo ($userTester->login("", "") === false) ? "PASSED ✅\n" : "FAILED ❌\n";

echo "Test 2: Kredensial Salah -> ";
echo ($userTester->login("salah_user", "salah_pass") === false) ? "PASSED ✅\n" : "FAILED ❌\n";

echo "------------------------------------------------\n";
echo "Testing Selesai.\n";
echo "</pre>";