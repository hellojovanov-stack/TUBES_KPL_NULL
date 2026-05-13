<?php
require_once '../controllers/ObatController.php';

$obat = new ObatController();
$action = $_GET['action'] ?? '';

switch($action) {
    case 'create':
        $obat->store();
        break;
    case 'edit':
        $obat->edit();
        break;
    case 'delete':
        $obat->delete();
        break;
    case 'search':        
        $obat->search();
        break;
    default:
        $obat->index();
        break;
}