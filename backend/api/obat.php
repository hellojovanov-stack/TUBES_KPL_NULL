<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../models/Obat.php';

$method = $_SERVER['REQUEST_METHOD'];
$obat = new Obat();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $result = $obat->getById((int)$_GET['id']);
                if ($result) {
                    echo json_encode(['status' => true, 'data' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => false, 'message' => 'Not found']);
                }
            } else {
                $list = $obat->getAll();
                echo json_encode(['status' => true, 'data' => $list]);
            }
            break;
        case 'POST':
            $data = $_POST;
            if(empty($data)) {
                $data = json_decode(file_get_contents('php://input'), true) ?? [];
            }
            
            $nama = $data['nama_obat'] ?? '';
            $kategori = $data['kategori'] ?? '';
            $id_kategori = isset($data['id_kategori']) && $data['id_kategori'] !== '' ? (int)$data['id_kategori'] : null;
            $id_supplier = isset($data['id_supplier']) && $data['id_supplier'] !== '' ? (int)$data['id_supplier'] : null;
            $stok = (int)($data['stok'] ?? 0);
            $harga = (int)($data['harga'] ?? 0);
            
            $gambar = null;
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
                $gambar = time() . "_" . $_FILES['gambar']['name'];
                $tmp = $_FILES['gambar']['tmp_name'];
                // Upload path should be relative to frontend/uploads or similar
                // But typically API is in backend/api/
                $upload_path = __DIR__ . "/../../frontend/uploads/" . $gambar;
                move_uploaded_file($tmp, $upload_path);
            }

            $success = $obat->create($nama, $kategori, $id_kategori, $id_supplier, $stok, $harga, $gambar);
            echo json_encode(['status' => $success]);
            break;
        case 'PUT':
            // To handle file upload in PUT requests, usually it's passed as POST with _method=PUT
            $data = json_decode(file_get_contents('php://input'), true);
            if(empty($data)) {
                $data = $_POST;
            }
            
            $id = $data['id'] ?? 0;
            $nama = $data['nama_obat'] ?? '';
            $kategori = $data['kategori'] ?? '';
            $id_kategori = isset($data['id_kategori']) && $data['id_kategori'] !== '' ? (int)$data['id_kategori'] : null;
            $id_supplier = isset($data['id_supplier']) && $data['id_supplier'] !== '' ? (int)$data['id_supplier'] : null;
            $stok = (int)($data['stok'] ?? 0);
            $harga = (int)($data['harga'] ?? 0);
            
            $gambar = null;
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
                $gambar = time() . "_" . $_FILES['gambar']['name'];
                $tmp = $_FILES['gambar']['tmp_name'];
                $upload_path = __DIR__ . "/../../frontend/uploads/" . $gambar;
                move_uploaded_file($tmp, $upload_path);
            }

            $updated = $obat->update((int)$id, $nama, $kategori, $id_kategori, $id_supplier, $stok, $harga, $gambar);
            echo json_encode(['status' => $updated]);
            break;
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? ($_GET['id'] ?? 0);
            $deleted = $obat->delete((int)$id);
            echo json_encode(['status' => $deleted]);
            break;
        default:
            http_response_code(405);
            echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}
?>
