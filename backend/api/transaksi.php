<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../models/Transaksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$transaksi = new Transaksi();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $result = $transaksi->getById((int)$_GET['id']);
                if ($result) {
                    echo json_encode(['status' => true, 'data' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => false, 'message' => 'Not found']);
                }
            } else if (isset($_GET['id_riwayat'])) {
                $list = $transaksi->getByRiwayatId((int)$_GET['id_riwayat']);
                echo json_encode(['status' => true, 'data' => $list]);
            } else {
                $list = $transaksi->getAll();
                echo json_encode(['status' => true, 'data' => $list]);
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $id_obat = $data['id_obat'] ?? 0;
            $jumlah = $data['jumlah'] ?? 1;
            $sub_total = $data['sub_total'] ?? 0;
            $id_riwayat = $data['id_riwayat'] ?? null;
            $user_id = $data['user_id'] ?? null;
            
            $id = $transaksi->create($id_obat, $jumlah, $sub_total, $id_riwayat, $user_id);
            echo json_encode(['status' => true, 'id' => $id]);
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
