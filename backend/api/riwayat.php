<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../models/RiwayatTransaksi.php';

$method = $_SERVER['REQUEST_METHOD'];
$riwayat = new RiwayatTransaksi();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $result = $riwayat->getById((int)$_GET['id']);
                if ($result) {
                    echo json_encode(['status' => true, 'data' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => false, 'message' => 'Not found']);
                }
            } else {
                $list = $riwayat->getAll();
                echo json_encode(['status' => true, 'data' => $list]);
            }
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $total_bayar = $data['total_bayar'] ?? 0;
            $jumlah_item = $data['jumlah_item'] ?? 0;
            $kasir = $data['kasir'] ?? 'admin';
            $id = $riwayat->create($total_bayar, $jumlah_item, $kasir);
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
