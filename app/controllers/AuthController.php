<?php

session_start();

require_once "../app/models/User.php";

class AuthController {

    private $user;

    public function __construct() {

        $this->user = new User();
    }

    public function login() {

        if($_SERVER['REQUEST_METHOD'] == 'POST') {

            $user = $this->user->login(
                $_POST['username'],
                $_POST['password']
            );

            if($user) {

                $_SESSION['user'] = $user;

                header("Location: index.php?page=dashboard");

            } else {

                $error = "Username atau password salah";

                include "../app/views/login.php";
            }

        } else {

            include "../app/views/login.php";
        }
    }

    public function logout() {

        session_destroy();

        header("Location: index.php");
    }
}