<?php

require_once "Database.php";
require_once __DIR__ . "/../helpers/DbC.php";
require_once __DIR__ . "/../helpers/TableDrivenValidator.php";

class Kategori {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
        $query = "SELECT * FROM kategori ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        // DbC: Precondition
        DbC::requireValidId((int)$id, 'id');

        $query = "SELECT * FROM kategori WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($nama, $deskripsi = null) {
        // DbC: Precondition via TableDrivenValidator
        TableDrivenValidator::validateOrFail('kategori', ['nama_kategori' => $nama]);

        $query = "INSERT INTO kategori (nama_kategori, deskripsi) VALUES (:nama, :deskripsi)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':nama'      => htmlspecialchars(trim($nama)),
            ':deskripsi' => $deskripsi
        ]);

        $id = $this->conn->lastInsertId();
        // DbC: Postcondition
        DbC::ensure((int)$id > 0, 'create() harus menghasilkan ID yang valid');

        return $id;
    }

    public function update($id, $nama, $deskripsi = null) {
        // DbC: Preconditions
        DbC::requireValidId((int)$id, 'id');
        TableDrivenValidator::validateOrFail('kategori', ['nama_kategori' => $nama]);

        $query = "UPDATE kategori SET nama_kategori=:nama, deskripsi=:deskripsi WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id'        => $id,
            ':nama'      => htmlspecialchars(trim($nama)),
            ':deskripsi' => $deskripsi
        ]);
    }

    public function delete($id) {
        // DbC: Precondition
        DbC::requireValidId((int)$id, 'id');

        $query = "DELETE FROM kategori WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
?>
