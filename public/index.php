<?php

session_start();

require_once "../app/controllers/AuthController.php";
require_once "../app/controllers/ObatController.php";

$page = $_GET['page'] ?? 'login';

$auth = new AuthController();
$obat = new ObatController();

switch($page) {

    case 'login':
        $auth->login();
        break;

    case 'logout':
        $auth->logout();
        break;

    case 'dashboard':
        $obat->dashboard();
        break;

    case 'tambah-obat':
        $obat->tambah();
        break;

    case 'edit-obat':
        $obat->edit();
        break;

    case 'update-obat':
        $obat->update();
        break;

    case 'delete-obat':
        $obat->delete();
        break;

    default:
        echo "404";
}