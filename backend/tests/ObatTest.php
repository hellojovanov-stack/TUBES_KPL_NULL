<?php

require_once __DIR__ . "/../models/Obat.php";

echo "UNIT TEST OBAT\n";

$obatModel = new Obat();

/*
|--------------------------------------------------------------------------
| TEST GET ALL
|--------------------------------------------------------------------------
*/

$data = $obatModel->getAll();

if (is_array($data)) {

    echo "TEST GET ALL = BERHASIL\n";

} else {

    echo "TEST GET ALL = GAGAL\n";
}

/*
|--------------------------------------------------------------------------
| TEST CREATE
|--------------------------------------------------------------------------
*/

$create = $obatModel->create(
    "Paracetamol Test",
    "Tablet",
    10,
    5000,
    ""
);

if ($create) {

    echo "TEST CREATE = BERHASIL\n";

} else {

    echo "TEST CREATE = GAGAL\n";
}

/*
|--------------------------------------------------------------------------
| TEST SEARCH
|--------------------------------------------------------------------------
*/

$search = $obatModel->search("Paracetamol");

if (is_array($search)) {

    echo "TEST SEARCH = BERHASIL\n";

} else {

    echo "TEST SEARCH = GAGAL\n";
}