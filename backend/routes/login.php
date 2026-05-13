<?php

session_start();

header("Content-Type: application/json");

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/../controllers/AuthController.php";

$auth = new AuthController();

try {
    $auth->login();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}