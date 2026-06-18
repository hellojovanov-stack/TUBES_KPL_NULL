<?php

<<<<<<< HEAD
class AuthMiddleware
{
    /**
     * Cek Request Login
     * @param bool $returnJson Jika true, kirim JSON response saat gagal
     */
    public static function check(bool $returnJson = true): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
            if ($returnJson) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode([
                    'status'  => false,
                    'message' => 'Unauthorized: silakan login terlebih dahulu'
                ]);
                exit;
            }
            header('Location: /TUBES_KPL_NULL/frontend/pages/login.php');
            exit;
        }
    }

    /**
     * @return string Username atau 'guest' jika tidak ada sesi
     */
    public static function currentUser(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['username'] ?? 'guest';
    }

    /**
     * Cek apakah sesi login sedang aktif (tanpa menghentikan eksekusi).
     *
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['login']) && $_SESSION['login'] === true;
    }
}
?>
=======
class AuthMiddleware {

    public static function check() {
        session_start();
        if(!isset($_SESSION['login'])) {
            header("Location: ../frontend/pages/login.php");
            exit;
        }
    }
}
>>>>>>> aa57d194d678ea3e8d159ee62948300d359ec6f2
