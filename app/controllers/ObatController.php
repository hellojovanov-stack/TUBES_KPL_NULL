<?php
require_once "../app/models/Obat.php";
class ObatController {
    private $obat;

    public function __construct() {
        $this->obat = new Obat();
    }

    public function dashboard() {

        $data = $this->obat->getAll();

        include "../app/views/dashboard.php";
    }

    public function tambah() {

        if($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->obat->create(
                $_POST['nama_obat'],
                $_POST['kategori'],
                $_POST['stok'],
                $_POST['harga']
            );

            header("Location: index.php?page=dashboard");
        }
    }

    public function edit() {

        $id = $_GET['id'];

        $obat = $this->obat->getById($id);

        include "../app/views/edit_obat.php";
    }

    public function update() {

        $this->obat->update(
            $_POST['id'],
            $_POST['nama_obat'],
            $_POST['kategori'],
            $_POST['stok'],
            $_POST['harga']
        );

        header("Location: index.php?page=dashboard");
    }

    public function delete() {

        $id = $_GET['id'];

        $this->obat->delete($id);

        header("Location: index.php?page=dashboard");
    }
}