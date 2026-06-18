<?php

require_once "Database.php";
require_once __DIR__ . "/../helpers/DbC.php";
require_once __DIR__ . "/../helpers/TableDrivenValidator.php";

class Supplier {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
        $query = "SELECT * FROM supplier ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        DbC::requireValidId((int)$id, 'id');

        $query = "SELECT * FROM supplier WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($nama, $alamat = null, $telepon = null) {
        TableDrivenValidator::validateOrFail('supplier', [
            'nama_supplier' => $nama,
            'telepon'       => $telepon ?? '',
        ]);

        $query = "INSERT INTO supplier (nama_supplier, alamat, telepon) VALUES (:nama, :alamat, :telepon)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':nama'    => htmlspecialchars(trim($nama)),
            ':alamat'  => $alamat,
            ':telepon' => $telepon
        ]);

        $id = $this->conn->lastInsertId();
        DbC::ensure((int)$id > 0, 'create() harus menghasilkan ID yang valid');

        return $id;
    }

    public function update($id, $nama, $alamat = null, $telepon = null) {
        DbC::requireValidId((int)$id, 'id');
        TableDrivenValidator::validateOrFail('supplier', [
            'nama_supplier' => $nama,
            'telepon'       => $telepon ?? '',
        ]);

        $query = "UPDATE supplier SET nama_supplier=:nama, alamat=:alamat, telepon=:telepon WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id'      => $id,
            ':nama'    => htmlspecialchars(trim($nama)),
            ':alamat'  => $alamat,
            ':telepon' => $telepon
        ]);
    }

    public function delete($id) {
        DbC::requireValidId((int)$id, 'id');

        $query = "DELETE FROM supplier WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
?>
