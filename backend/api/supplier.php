<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../models/Supplier.php';

$method = $_SERVER['REQUEST_METHOD'];
$supplier = new Supplier();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $result = $supplier->getById((int)$_GET['id']);
                if ($result) {
                    echo json_encode(['status' => true, 'data' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => false, 'message' => 'Not found']);
                }
            } else {
                $list = $supplier->getAll();
                echo json_encode(['status' => true, 'data' => $list]);
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $nama = $data['nama_supplier'] ?? '';
            $alamat = $data['alamat'] ?? null;
            $telepon = $data['telepon'] ?? null;
            $id = $supplier->create($nama, $alamat, $telepon);
            echo json_encode(['status' => true, 'id' => $id]);
            break;
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $nama = $data['nama_supplier'] ?? '';
            $alamat = $data['alamat'] ?? null;
            $telepon = $data['telepon'] ?? null;
            $updated = $supplier->update((int)$id, $nama, $alamat, $telepon);
            echo json_encode(['status' => $updated]);
            break;
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? ($_GET['id'] ?? 0);
            $deleted = $supplier->delete((int)$id);
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
