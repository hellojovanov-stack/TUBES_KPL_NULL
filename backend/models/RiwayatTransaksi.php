<?php

require_once "Database.php";
require_once __DIR__ . "/../helpers/DbC.php";
require_once __DIR__ . "/../helpers/TableDrivenValidator.php";

class RiwayatTransaksi {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
        $query = "SELECT * FROM riwayat_transaksi ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        DbC::requireValidId((int)$id, 'id');

        $query = "SELECT * FROM riwayat_transaksi WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($total_bayar, $jumlah_item, $kasir = 'admin') {
        // DbC: Preconditions
        DbC::requirePositive((int)$total_bayar, 'total_bayar');
        DbC::require((int)$jumlah_item >= 1, 'jumlah_item harus minimal 1');
        DbC::requireNonEmpty((string)$kasir, 'kasir');

        $query = "INSERT INTO riwayat_transaksi (total_bayar, jumlah_item, kasir) 
                  VALUES (:total_bayar, :jumlah_item, :kasir)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':total_bayar' => (int)$total_bayar,
            ':jumlah_item' => (int)$jumlah_item,
            ':kasir'       => htmlspecialchars(trim($kasir))
        ]);

        $id = $this->conn->lastInsertId();
        // DbC: Postcondition
        DbC::ensure((int)$id > 0, 'create() harus menghasilkan ID riwayat yang valid');

        return $id;
    }
}
?>
