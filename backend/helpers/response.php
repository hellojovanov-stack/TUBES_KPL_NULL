<?php

class Response {

    public static function json(
        $success,
        $message,
        $data = [],
        $statusCode = 200
    ) {
        // ==========================================
        // IMPLEMENTASI DEFENSIVE PROGRAMMING
        // ==========================================
        if (!is_int($statusCode)) {
            $statusCode = (int) $statusCode;
            if ($statusCode < 100 || $statusCode > 599) {
                $statusCode = 200;
            }
        }
        if (!is_string($message)) {
            $message = (string) $message;
        }
        // ==========================================
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            "success" => $success,
            "message" => $message,
            "data"    => $data
        ]);
        exit;
    }
}