<?php

require_once "Database.php";
require_once __DIR__ . "/../helpers/DbC.php";
require_once __DIR__ . "/../helpers/TableDrivenValidator.php";

class Obat {

    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
        $query = "SELECT obat.*, kategori.nama_kategori as nama_kategori_ref, supplier.nama_supplier 
                  FROM obat 
                  LEFT JOIN kategori ON obat.id_kategori = kategori.id 
                  LEFT JOIN supplier ON obat.id_supplier = supplier.id 
                  ORDER BY obat.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($nama, $kategori, $id_kategori, $id_supplier, $stok, $harga, $gambar = null) {
        // DbC Precondition via TableDrivenValidator
        TableDrivenValidator::validateOrFail('obat', [
            'nama_obat' => $nama,
            'stok'      => $stok,
            'harga'     => $harga,
        ]);

        $query = "INSERT INTO obat
        (nama_obat, kategori, id_kategori, id_supplier, stok, harga, gambar)
        VALUES
        (:nama, :kategori, :id_kategori, :id_supplier, :stok, :harga, :gambar)";

        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([
            ':nama'        => htmlspecialchars(trim($nama)),
            ':kategori'    => $kategori,
            ':id_kategori' => $id_kategori,
            ':id_supplier' => $id_supplier,
            ':stok'        => (int)$stok,
            ':harga'       => (int)$harga,
            ':gambar'      => $gambar
        ]);

        // DbC Postcondition
        DbC::ensure($result !== false, 'create() harus berhasil dieksekusi');

        return $this->conn->lastInsertId();
    }

    public function delete($id) {
        DbC::requireValidId((int)$id, 'id');

        $query = "DELETE FROM obat WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public function getById($id) {
        DbC::requireValidId((int)$id, 'id');

        $query = "SELECT * FROM obat WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $nama, $kategori, $id_kategori, $id_supplier, $stok, $harga, $gambar = null) {
        DbC::requireValidId((int)$id, 'id');
        TableDrivenValidator::validateOrFail('obat', [
            'nama_obat' => $nama,
            'stok'      => $stok,
            'harga'     => $harga,
        ]);

        if ($gambar !== null) {
            $query = "UPDATE obat
            SET nama_obat=:nama, kategori=:kategori, id_kategori=:id_kategori, id_supplier=:id_supplier, stok=:stok, harga=:harga, gambar=:gambar
            WHERE id=:id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':id'          => $id,
                ':nama'        => htmlspecialchars(trim($nama)),
                ':kategori'    => $kategori,
                ':id_kategori' => $id_kategori,
                ':id_supplier' => $id_supplier,
                ':stok'        => (int)$stok,
                ':harga'       => (int)$harga,
                ':gambar'      => $gambar
            ]);
        } else {
            $query = "UPDATE obat
            SET nama_obat=:nama, kategori=:kategori, id_kategori=:id_kategori, id_supplier=:id_supplier, stok=:stok, harga=:harga
            WHERE id=:id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':id'          => $id,
                ':nama'        => htmlspecialchars(trim($nama)),
                ':kategori'    => $kategori,
                ':id_kategori' => $id_kategori,
                ':id_supplier' => $id_supplier,
                ':stok'        => (int)$stok,
                ':harga'       => (int)$harga,
            ]);
        }
    }
}
?>