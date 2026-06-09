<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../models/User.php';

$method = $_SERVER['REQUEST_METHOD'];
$user = new User();

try {
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if(empty($data)) {
            $data = $_POST;
        }

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $loggedInUser = $user->login($username, $password);

        if ($loggedInUser) {
            // For a simple setup, start session and set user, but since this is an API, 
            // returning user info (avoiding sending password hash)
            unset($loggedInUser['password']);
            echo json_encode(['status' => true, 'message' => 'Login successful', 'data' => $loggedInUser]);
        } else {
            http_response_code(401);
            echo json_encode(['status' => false, 'message' => 'Invalid username or password']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}
?>
