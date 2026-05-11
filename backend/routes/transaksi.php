<?php

require_once '../controllers/TransactionController.php';

$transaction = new TransactionController();

$action = $_GET['action'] ?? '';

switch($action) {

    case 'add':
        $transaction->addToCart();
        break;

    case 'pay':
        $transaction->pay();
        break;

    case 'cancel':
        $transaction->cancel();
        break;

    default:
        $transaction->index();
}