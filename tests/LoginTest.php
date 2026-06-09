<?php

/**
 * tests/LoginTest.php — Unit Test untuk Model User / Autentikasi
 */

runSection('LoginTest — Model User login()', function () {
    $user = new User();

    // Login dengan kredensial salah
    $result = $user->login('user_tidak_ada_xyz123', 'wrongpassword');
    assertFalse((bool)$result, 'login() mengembalikan false untuk kredensial salah');

    // Login dengan username kosong
    $result = $user->login('', '');
    assertFalse((bool)$result, 'login() mengembalikan false untuk username/password kosong');
});

runSection('LoginTest — Validasi Input User (TableDrivenValidator)', function () {
    $valid = TableDrivenValidator::validate('user', [
        'username' => 'admin',
        'password' => 'admin123',
    ]);
    assertTrue($valid['valid'], 'User valid lolos validasi');

    $r = TableDrivenValidator::validate('user', ['username' => '', 'password' => 'abc']);
    assertFalse($r['valid'], 'Username kosong gagal validasi');

    $r = TableDrivenValidator::validate('user', ['username' => 'admin', 'password' => '']);
    assertFalse($r['valid'], 'Password kosong gagal validasi');

    $r = TableDrivenValidator::validate('user', ['username' => 'admin', 'password' => '12']);
    assertFalse($r['valid'], 'Password < 4 karakter gagal validasi');

    $r = TableDrivenValidator::validate('user', ['username' => str_repeat('a', 101), 'password' => 'pass1234']);
    assertFalse($r['valid'], 'Username > 100 karakter gagal validasi');
});

runSection('LoginTest — DbC password_hash & password_verify', function () {
    $password   = 'test_password_123';
    $hashed     = password_hash($password, PASSWORD_BCRYPT);
    $isVerified = password_verify($password, $hashed);

    assertTrue($isVerified, 'password_verify() berhasil memverifikasi hash yang valid');
    assertFalse(password_verify('wrong_password', $hashed), 'password_verify() menolak password yang salah');
    assertTrue(strlen($hashed) >= 60, 'Hash bcrypt panjangnya >= 60 karakter');
});
?>
