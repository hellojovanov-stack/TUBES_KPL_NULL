<?php

session_start();

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Performance.php';

class AuthController {

    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        try {
            Validator::required($_POST['username'] ?? '', 'Username');
            Validator::required($_POST['password'] ?? '', 'Password');

            $user = $this->userModel->login(
                $_POST['username'],
                $_POST['password']
            );

            if (!$user) {
                return Response::json(false, "Username / Password salah");
            }

            $_SESSION['login'] = true;
            $_SESSION['username'] = $user['username'];

            return Response::json(true, "Login berhasil");

        } catch (Exception $e) {
            return Response::json(false, $e->getMessage());
        }
    }

    // TAMBAH METHOD LOGOUT
    public function logout() {
        session_start();
        $_SESSION = [];
        session_destroy();
        
        // Cek apakah request dari AJAX atau langsung
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return Response::json(true, "Logout berhasil");
        } else {
            header("Location: ../../frontend/pages/login.php");
            exit;
        }
    }
}