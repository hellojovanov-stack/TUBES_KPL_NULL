<?php
// backend/api/transaction.php
// API endpoint untuk transaksi

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Inisialisasi cart di session
if (!isset($_SESSION['api_cart'])) {
    $_SESSION['api_cart'] = [];
}

// Data obat (sama dengan search API)
$dataObat = [
    ["id" => 1, "nama_obat" => "Paracetamol", "kategori" => "Tablet", "stok" => 100, "harga" => 5000],
    ["id" => 2, "nama_obat" => "Amoxilin", "kategori" => "Kapsul", "stok" => 50, "harga" => 15000],
    ["id" => 3, "nama_obat" => "Antimo", "kategori" => "Tablet", "stok" => 75, "harga" => 8000],
    ["id" => 4, "nama_obat" => "Promag", "kategori" => "Tablet", "stok" => 120, "harga" => 7000],
    ["id" => 5, "nama_obat" => "Bodrex", "kategori" => "Tablet", "stok" => 60, "harga" => 6000],
    ["id" => 6, "nama_obat" => "Sanmol", "kategori" => "Sirup", "stok" => 40, "harga" => 25000],
    ["id" => 7, "nama_obat" => "Oskadon", "kategori" => "Tablet", "stok" => 90, "harga" => 5500],
    ["id" => 8, "nama_obat" => "Mylanta", "kategori" => "Sirup", "stok" => 30, "harga" => 35000]
];

// TABLE-DRIVEN ROUTING (untuk teknik Table-driven)
switch ($action) {
    case 'list':
        listObat();
        break;
    case 'search':
        searchObat();
        break;
    case 'add':
        if ($method === 'POST') addToCart();
        else methodNotAllowed();
        break;
    case 'cart':
        getCart();
        break;
    case 'checkout':
        if ($method === 'POST') checkout();
        else methodNotAllowed();
        break;
    case 'clear':
        clearCart();
        break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint tidak ditemukan',
            'available_actions' => ['list', 'search', 'add', 'cart', 'checkout', 'clear']
        ]);
}

function listObat() {
    global $dataObat;
    echo json_encode([
        'success' => true,
        'data' => $dataObat,
        'total' => count($dataObat),
        'message' => 'Daftar obat'
    ]);
}

function searchObat() {
    global $dataObat;
    $keyword = $_GET['keyword'] ?? '';
    
    if (empty($keyword)) {
        listObat();
        return;
    }
    
    $results = array_filter($dataObat, function($obat) use ($keyword) {
        return stripos($obat['nama_obat'], $keyword) !== false || 
               stripos($obat['kategori'], $keyword) !== false;
    });
    
    $results = array_values($results);
    
    echo json_encode([
        'success' => count($results) > 0,
        'data' => $results,
        'total' => count($results),
        'keyword' => $keyword,
        'message' => count($results) > 0 ? 'Data ditemukan' : 'Data tidak ditemukan'
    ]);
}

function addToCart() {
    global $dataObat;
    
    $id_obat = (int)($_POST['id_obat'] ?? $_GET['id_obat'] ?? 0);
    $jumlah = (int)($_POST['jumlah'] ?? $_GET['jumlah'] ?? 1);
    
    // Cari obat
    $obat = null;
    foreach ($dataObat as $item) {
        if ($item['id'] == $id_obat) {
            $obat = $item;
            break;
        }
    }
    
    if (!$obat) {
        echo json_encode(['success' => false, 'message' => 'Obat tidak ditemukan']);
        return;
    }
    
    // Cek stok
    if ($jumlah > $obat['stok']) {
        echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi']);
        return;
    }
    
    // Cek apakah sudah ada di cart
    $found = false;
    foreach ($_SESSION['api_cart'] as &$item) {
        if ($item['id'] == $id_obat) {
            $item['jumlah'] += $jumlah;
            $item['subtotal'] = $item['harga'] * $item['jumlah'];
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['api_cart'][] = [
            'id' => $obat['id'],
            'nama' => $obat['nama_obat'],
            'harga' => $obat['harga'],
            'jumlah' => $jumlah,
            'subtotal' => $obat['harga'] * $jumlah
        ];
    }
    
    $total = array_sum(array_column($_SESSION['api_cart'], 'subtotal'));
    $cartCount = count($_SESSION['api_cart']);
    $state = $cartCount > 0 ? 'PENDING' : 'DRAFT';
    
    echo json_encode([
        'success' => true,
        'message' => 'Berhasil tambah ke keranjang',
        'cart' => $_SESSION['api_cart'],
        'total' => $total,
        'cart_count' => $cartCount,
        'transaction_state' => $state
    ]);
}

function getCart() {
    $total = array_sum(array_column($_SESSION['api_cart'], 'subtotal'));
    $state = count($_SESSION['api_cart']) > 0 ? 'PENDING' : 'DRAFT';
    
    echo json_encode([
        'success' => true,
        'cart' => $_SESSION['api_cart'],
        'total' => $total,
        'cart_count' => count($_SESSION['api_cart']),
        'transaction_state' => $state
    ]);
}

function checkout() {
    if (empty($_SESSION['api_cart'])) {
        echo json_encode(['success' => false, 'message' => 'Keranjang kosong']);
        return;
    }
    
    $_SESSION['api_cart'] = [];
    
    echo json_encode([
        'success' => true,
        'message' => 'Pembayaran berhasil! Terima kasih.',
        'transaction_state' => 'COMPLETED'
    ]);
}

function clearCart() {
    $_SESSION['api_cart'] = [];
    echo json_encode([
        'success' => true,
        'message' => 'Transaksi dibatalkan',
        'transaction_state' => 'CANCELLED'
    ]);
}

function methodNotAllowed() {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan. Gunakan POST.']);
}
?>