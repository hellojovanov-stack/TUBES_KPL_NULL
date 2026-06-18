<?php

session_start();

require_once __DIR__ . '/../models/Transaksi.php';
require_once __DIR__ . '/../models/Obat.php';

class TransaksiController {

    private $transaksiModel;
    private $obatModel;

    public function __construct() {

        $this->transaksiModel = new Transaksi();
        $this->obatModel      = new Obat();

        if (!isset($_SESSION['cart'])) {

            $_SESSION['cart'] = [];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ADD TO CART
    |--------------------------------------------------------------------------
    */

    public function tambah() {

        $id_obat = (int) ($_POST['id_obat'] ?? 0);
        $jumlah  = (int) ($_POST['jumlah'] ?? 0);

        if ($id_obat <= 0 || $jumlah <= 0) {

            return [
                "success" => false,
                "message" => "Input transaksi tidak valid"
            ];
        }

        $obat = $this->obatModel->getById($id_obat);

        if (!$obat) {

            return [
                "success" => false,
                "message" => "Obat tidak ditemukan"
            ];
        }

        if ($jumlah > $obat['stok']) {

            return [
                "success" => false,
                "message" => "Stok tidak mencukupi"
            ];
        }

        $_SESSION['cart'][] = [

            "id"       => $obat['id'],
            "nama"     => $obat['nama_obat'],
            "harga"    => $obat['harga'],
            "jumlah"   => $jumlah,
            "subtotal" => $obat['harga'] * $jumlah
        ];

        return [
            "success" => true,
            "message" => "Berhasil tambah ke keranjang"
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CHECKOUT
    |--------------------------------------------------------------------------
    */

    public function bayar() {

        if (empty($_SESSION['cart'])) {

            return [
                "success" => false,
                "message" => "Keranjang kosong"
            ];
        }

        foreach ($_SESSION['cart'] as $item) {

            $this->transaksiModel->create(
                $item['id'],
                $item['jumlah'],
                $item['subtotal']
            );

            $this->obatModel->reduceStock(
                $item['id'],
                $item['jumlah']
            );
        }

        unset($_SESSION['cart']);

        return [
            "success" => true,
            "message" => "Pembayaran berhasil"
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL
    |--------------------------------------------------------------------------
    */

    public function batal() {

        unset($_SESSION['cart']);

        return [
            "success" => true,
            "message" => "Transaksi dibatalkan"
        ];
    }
}