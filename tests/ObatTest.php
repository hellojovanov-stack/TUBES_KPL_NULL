<?php

/**
 * tests/ObatTest.php — Unit Test untuk Model Obat
 */

runSection('ObatTest — Validasi Input (TableDrivenValidator)', function () {
    // Valid data
    $valid = TableDrivenValidator::validate('obat', [
        'nama_obat' => 'Paracetamol',
        'stok'      => 100,
        'harga'     => 5000,
    ]);
    assertTrue($valid['valid'], 'Obat valid lolos validasi');

    // Nama kosong
    $r = TableDrivenValidator::validate('obat', ['nama_obat' => '', 'stok' => 10, 'harga' => 1000]);
    assertFalse($r['valid'], 'Obat nama kosong gagal validasi');

    // Stok negatif
    $r = TableDrivenValidator::validate('obat', ['nama_obat' => 'X', 'stok' => -1, 'harga' => 1000]);
    assertFalse($r['valid'], 'Obat stok negatif gagal validasi');

    // Harga negatif
    $r = TableDrivenValidator::validate('obat', ['nama_obat' => 'X', 'stok' => 0, 'harga' => -500]);
    assertFalse($r['valid'], 'Obat harga negatif gagal validasi');
});

runSection('ObatTest — Model CRUD', function () {
    $obat = new Obat();

    // getAll() harus return array
    $all = $obat->getAll();
    assertTrue(is_array($all), 'getAll() mengembalikan array');

    // getById dengan id tidak valid
    $notFound = $obat->getById(999999);
    assertFalse((bool)$notFound, 'getById(999999) mengembalikan false/empty');

    // create() dengan data valid
    $id = $obat->create('TestObatUnit', 'Umum', null, null, 50, 3000);
    assertTrue($id !== false && (int)$id > 0, 'create() berhasil dan mengembalikan ID valid');

    if ($id) {
        // getById setelah create
        $found = $obat->getById((int)$id);
        assertTrue(!empty($found), 'getById() menemukan data yang baru dibuat');
        assertEquals('TestObatUnit', $found['nama_obat'] ?? '', 'nama_obat tersimpan dengan benar');

        // update()
        $updated = $obat->update((int)$id, 'TestObatUpdated', 'Umum', null, null, 75, 4000);
        assertTrue($updated, 'update() berhasil');

        $after = $obat->getById((int)$id);
        assertEquals('TestObatUpdated', $after['nama_obat'] ?? '', 'nama_obat terupdate dengan benar');
        assertEquals(75, (int)($after['stok'] ?? 0), 'stok terupdate dengan benar');

        // delete() — cleanup
        $deleted = $obat->delete((int)$id);
        assertTrue($deleted, 'delete() berhasil');

        $afterDel = $obat->getById((int)$id);
        assertFalse((bool)$afterDel, 'Data sudah terhapus setelah delete()');
    }
});

runSection('ObatTest — DbC Precondition', function () {
    // DbC::requireNonEmpty harus throw pada string kosong
    $threw = false;
    try {
        DbC::requireNonEmpty('', 'nama_obat');
    } catch (InvalidArgumentException $e) {
        $threw = true;
    }
    assertTrue($threw, 'DbC::requireNonEmpty() melempar exception untuk string kosong');

    // DbC::requirePositive harus throw pada nilai negatif
    $threw = false;
    try {
        DbC::requirePositive(-1, 'stok');
    } catch (InvalidArgumentException $e) {
        $threw = true;
    }
    assertTrue($threw, 'DbC::requirePositive() melempar exception untuk nilai negatif');
});
?>
