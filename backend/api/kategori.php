<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../models/Kategori.php';

$method = $_SERVER['REQUEST_METHOD'];
$kategori = new Kategori();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $result = $kategori->getById((int)$_GET['id']);
                if ($result) {
                    echo json_encode(['status' => true, 'data' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => false, 'message' => 'Not found']);
                }
            } else {
                $list = $kategori->getAll();
                echo json_encode(['status' => true, 'data' => $list]);
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $nama = $data['nama_kategori'] ?? ($data['nama'] ?? '');
            $deskripsi = $data['deskripsi'] ?? null;
            $id = $kategori->create($nama, $deskripsi);
            echo json_encode(['status' => true, 'id' => $id]);
            break;
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $nama = $data['nama_kategori'] ?? ($data['nama'] ?? '');
            $deskripsi = $data['deskripsi'] ?? null;
            $updated = $kategori->update((int)$id, $nama, $deskripsi);
            echo json_encode(['status' => $updated]);
            break;
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $deleted = $kategori->delete((int)$id);
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