<?php

class AuthMiddleware {

    public static function check() {
        session_start();
        if(!isset($_SESSION['login'])) {
            header("Location: ../frontend/pages/login.php");
            exit;
        }
    }
}