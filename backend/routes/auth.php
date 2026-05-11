<?php

require_once "../controllers/AuthController.php";

$controller = new AuthController();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $controller->login();
        break;
    case 'logout':
        $controller->logout();  // SEKARANG SUDAH ADA
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode([
            "success" => false,
            "message" => "Route tidak ditemukan"
        ]);
}