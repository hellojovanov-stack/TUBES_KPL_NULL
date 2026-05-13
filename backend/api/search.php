<?php
// backend/api/v1/search.php
// API endpoint untuk pencarian obat
// METHOD: GET
// Parameter: ?keyword=paracetamol

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Data obat dari JSON (sebagai database)
$dataObat = [
    [
        "id" => 1,
        "nama_obat" => "Paracetamol",
        "kategori" => "Tablet",
        "stok" => 100,
        "harga" => 5000
    ],
    [
        "id" => 2,
        "nama_obat" => "Amoxilin",
        "kategori" => "Kapsul",
        "stok" => 50,
        "harga" => 15000
    ],
    [
        "id" => 3,
        "nama_obat" => "Antimo",
        "kategori" => "Tablet",
        "stok" => 75,
        "harga" => 8000
    ],
    [
        "id" => 4,
        "nama_obat" => "Promag",
        "kategori" => "Tablet",
        "stok" => 120,
        "harga" => 7000
    ],
    [
        "id" => 5,
        "nama_obat" => "Bodrex",
        "kategori" => "Tablet",
        "stok" => 60,
        "harga" => 6000
    ]
];

$keyword = $_GET['keyword'] ?? '';

if (empty($keyword)) {
    echo json_encode([
        'success' => true,
        'data' => $dataObat,
        'total' => count($dataObat),
        'message' => 'Menampilkan semua obat'
    ]);
    exit;
}

// Filter berdasarkan keyword
$results = array_filter($dataObat, function($obat) use ($keyword) {
    return stripos($obat['nama_obat'], $keyword) !== false || 
           stripos($obat['kategori'], $keyword) !== false;
});

$results = array_values($results);

if (count($results) > 0) {
    echo json_encode([
        'success' => true,
        'data' => $results,
        'total' => count($results),
        'keyword' => $keyword,
        'message' => 'Ditemukan ' . count($results) . ' data'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'data' => [],
        'total' => 0,
        'keyword' => $keyword,
        'message' => 'Data tidak ditemukan'
    ]);
}
?>