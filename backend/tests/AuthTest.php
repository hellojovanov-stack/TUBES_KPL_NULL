<?php

require_once __DIR__ . "/../models/User.php";

echo "UNIT TEST LOGIN\n";

$userModel = new User();

/*
|--------------------------------------------------------------------------
| TEST 1
|--------------------------------------------------------------------------
*/

$result1 = $userModel->login("admin", "admin123");

if ($result1) {

    echo "TEST LOGIN VALID = BERHASIL\n";

} else {

    echo "TEST LOGIN VALID = GAGAL\n";
}

/*
|--------------------------------------------------------------------------
| TEST 2
|--------------------------------------------------------------------------
*/

$result2 = $userModel->login("salah", "123");

if (!$result2) {

    echo "TEST LOGIN INVALID = BERHASIL\n";

} else {

    echo "TEST LOGIN INVALID = GAGAL\n";
}