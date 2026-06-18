<?php
<<<<<<< HEAD

require_once "Database.php";
require_once __DIR__ . "/../helpers/DbC.php";
require_once __DIR__ . "/../helpers/TableDrivenValidator.php";

class Obat {

=======
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/PerformanceLogger.php';

class Obat {
>>>>>>> aa57d194d678ea3e8d159ee62948300d359ec6f2
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
<<<<<<< HEAD
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
=======
        $start = PerformanceLogger::start('obat_getAll');
        
        $query = "SELECT * FROM obat ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        
        PerformanceLogger::end('obat_getAll');
        
        return $result;
    }
    
    // Method khusus untuk performance testing bulk insert
    public function bulkInsert($count = 100) {
        $start = PerformanceLogger::start("obat_bulkInsert_{$count}");
        
        $query = "INSERT INTO obat (nama_obat, kategori, stok, harga, gambar) 
                  VALUES (:nama, :kategori, :stok, :harga, :gambar)";
        $stmt = $this->conn->prepare($query);
        
        $totalTime = 0;
        for ($i = 1; $i <= $count; $i++) {
            $nama = "Test Obat {$i}";
            $kategori = "Test Kategori";
            $stok = rand(10, 100);
            $harga = rand(5000, 50000);
            $gambar = "";
            
            $itemStart = microtime(true);
            $stmt->bindParam(':nama', $nama);
            $stmt->bindParam(':kategori', $kategori);
            $stmt->bindParam(':stok', $stok);
            $stmt->bindParam(':harga', $harga);
            $stmt->bindParam(':gambar', $gambar);
            $stmt->execute();
            $totalTime += (microtime(true) - $itemStart);
        }
        
        PerformanceLogger::end("obat_bulkInsert_{$count}");
        
        return [
            'total_records' => $count,
            'total_time' => $totalTime,
            'avg_time_per_record' => $totalTime / $count
        ];
    }
    
    // Performance test untuk search dengan berbagai keyword length
    public function searchPerformanceTest() {
        $keywords = ['a', 'am', 'ame', 'amex', 'amex', 'Paracetamol'];
        $results = [];
        
        foreach ($keywords as $keyword) {
            $start = PerformanceLogger::start("obat_search_{$keyword}");
            
            $query = "SELECT * FROM obat WHERE nama_obat LIKE :keyword OR kategori LIKE :keyword";
            $stmt = $this->conn->prepare($query);
            $searchTerm = "%{$keyword}%";
            $stmt->bindParam(':keyword', $searchTerm);
            $stmt->execute();
            $data = $stmt->fetchAll();
            
            PerformanceLogger::end("obat_search_{$keyword}");
            
            $results[$keyword] = [
                'results_count' => count($data),
                'log' => PerformanceLogger::getLog("obat_search_{$keyword}")
            ];
        }
        
        return $results;
    }

    // ... method lainnya tetap sama
}
>>>>>>> aa57d194d678ea3e8d159ee62948300d359ec6f2
