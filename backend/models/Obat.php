<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/PerformanceLogger.php';

class Obat {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
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