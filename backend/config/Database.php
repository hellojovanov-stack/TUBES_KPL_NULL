<?php

class Database {
    private $host = "localhost";
    private $db_name = "apotek_db";
    private $username = "root";
    private $password = "";
    private $connection;

    public function connect() {
        if ($this->connection === null) {
            try {
                // Pastikan port 3307 sesuai dengan setting MySQL di XAMPP
                $this->connection = new PDO(
                    "mysql:host={$this->host};port=3307;dbname={$this->db_name};charset=utf8",
                    $this->username,
                    $this->password
                );
                $this->connection->setAttribute(
                    PDO::ATTR_ERRMODE, 
                    PDO::ERRMODE_EXCEPTION
                );
                $this->connection->setAttribute(
                    PDO::ATTR_DEFAULT_FETCH_MODE,
                    PDO::FETCH_ASSOC
                );
            } catch (PDOException $e) {
                die("Koneksi database gagal : " . $e->getMessage());
            }
        }
        return $this->connection;
    }
}