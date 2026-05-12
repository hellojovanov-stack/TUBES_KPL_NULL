<?php

require_once "Database.php";

class Obat {

    private $conn;

    public function __construct() {

        $database = new Database();

        $this->conn = $database->connect();
    }

    public function getAll() {

        $query = "SELECT * FROM obat ORDER BY id DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($nama,$kategori,$stok,$harga) {

        if(empty($nama) || $stok < 0 || $harga < 0) {
            return false;
        }
        $query = "INSERT INTO obat
        (nama_obat,kategori,stok,harga)
        VALUES
        (:nama,:kategori,:stok,:harga)";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':nama'=>$nama,
            ':kategori'=>$kategori,
            ':stok'=>$stok,
            ':harga'=>$harga
        ]);
    }

    public function delete($id) {

        $query = "DELETE FROM obat WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':id'=>$id
        ]);
    }

    public function getById($id) {

        $query = "SELECT * FROM obat WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->execute([
            ':id'=>$id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id,$nama,$kategori,$stok,$harga) {

        $query = "UPDATE obat
        SET
        nama_obat=:nama,
        kategori=:kategori,
        stok=:stok,
        harga=:harga
        WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':id'=>$id,
            ':nama'=>$nama,
            ':kategori'=>$kategori,
            ':stok'=>$stok,
            ':harga'=>$harga
        ]);
    }
}