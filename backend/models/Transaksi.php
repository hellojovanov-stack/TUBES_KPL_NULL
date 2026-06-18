<?php

require_once "Database.php";
require_once __DIR__ . "/../helpers/DbC.php";
require_once __DIR__ . "/../helpers/TableDrivenValidator.php";

class Transaksi {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Detect whether the transaksi table uses 'sub_total' or legacy 'total_harga'.
     */
    private function getSubTotalColumn(): string {
        $stmt = $this->conn->query("SHOW COLUMNS FROM transaksi LIKE 'sub_total'");
        return $stmt->rowCount() > 0 ? 'sub_total' : 'total_harga';
    }

    public function getAll() {
        $col = $this->getSubTotalColumn();
        $query = "SELECT transaksi.*, transaksi.$col AS sub_total, obat.nama_obat 
                  FROM transaksi 
                  LEFT JOIN obat ON transaksi.id_obat = obat.id 
                  ORDER BY transaksi.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        DbC::requireValidId((int)$id, 'id');

        $col = $this->getSubTotalColumn();
        $query = "SELECT transaksi.*, transaksi.$col AS sub_total, obat.nama_obat
                  FROM transaksi
                  LEFT JOIN obat ON transaksi.id_obat = obat.id
                  WHERE transaksi.id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByRiwayatId($id_riwayat) {
        DbC::requireValidId((int)$id_riwayat, 'id_riwayat');

        // Check if id_riwayat column exists
        $checkCol = $this->conn->query("SHOW COLUMNS FROM transaksi LIKE 'id_riwayat'");
        if ($checkCol->rowCount() === 0) {
            return [];
        }

        $col = $this->getSubTotalColumn();
        $query = "SELECT transaksi.*, transaksi.$col AS sub_total, obat.nama_obat 
                  FROM transaksi 
                  LEFT JOIN obat ON transaksi.id_obat = obat.id 
                  WHERE transaksi.id_riwayat=:id_riwayat 
                  ORDER BY transaksi.id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_riwayat' => $id_riwayat]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($id_obat, $jumlah, $sub_total, $id_riwayat = null, $user_id = null) {
        // DbC: Preconditions
        TableDrivenValidator::validateOrFail('transaksi', [
            'id_obat'   => $id_obat,
            'jumlah'    => $jumlah,
            'sub_total' => $sub_total,
        ]);

        $col = $this->getSubTotalColumn();

        $hasRiwayat = $this->conn->query("SHOW COLUMNS FROM transaksi LIKE 'id_riwayat'")->rowCount() > 0;
        $hasUserId  = $this->conn->query("SHOW COLUMNS FROM transaksi LIKE 'user_id'")->rowCount() > 0;

        if ($hasRiwayat && $hasUserId) {
            $query = "INSERT INTO transaksi (id_obat, jumlah, $col, id_riwayat, user_id) 
                      VALUES (:id_obat, :jumlah, :sub_total, :id_riwayat, :user_id)";
            $params = [':id_obat' => $id_obat, ':jumlah' => $jumlah, ':sub_total' => $sub_total,
                       ':id_riwayat' => $id_riwayat, ':user_id' => $user_id];
        } elseif ($hasRiwayat) {
            $query = "INSERT INTO transaksi (id_obat, jumlah, $col, id_riwayat) 
                      VALUES (:id_obat, :jumlah, :sub_total, :id_riwayat)";
            $params = [':id_obat' => $id_obat, ':jumlah' => $jumlah, ':sub_total' => $sub_total,
                       ':id_riwayat' => $id_riwayat];
        } else {
            $query = "INSERT INTO transaksi (id_obat, jumlah, $col) 
                      VALUES (:id_obat, :jumlah, :sub_total)";
            $params = [':id_obat' => $id_obat, ':jumlah' => $jumlah, ':sub_total' => $sub_total];
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        $id = $this->conn->lastInsertId();
        // DbC: Postcondition
        DbC::ensure((int)$id > 0, 'create() harus menghasilkan ID transaksi yang valid');

        return $id;
    }
}
?>
