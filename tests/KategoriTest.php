<?php

/**
 * tests/KategoriTest.php — Unit Test untuk Model Kategori
 */

runSection('KategoriTest — Validasi Input', function () {
    $valid = TableDrivenValidator::validate('kategori', [
        'nama_kategori' => 'Antibiotik',
        'deskripsi'     => 'Obat untuk infeksi bakteri',
    ]);
    assertTrue($valid['valid'], 'Kategori valid lolos validasi');

    $r = TableDrivenValidator::validate('kategori', ['nama_kategori' => '']);
    assertFalse($r['valid'], 'Kategori nama kosong gagal validasi');

    $r = TableDrivenValidator::validate('kategori', ['nama_kategori' => str_repeat('A', 101)]);
    assertFalse($r['valid'], 'Kategori nama > 100 karakter gagal validasi');
});

runSection('KategoriTest — Model CRUD', function () {
    $kategori = new Kategori();

    $all = $kategori->getAll();
    assertTrue(is_array($all), 'getAll() mengembalikan array');

    $notFound = $kategori->getById(999999);
    assertFalse((bool)$notFound, 'getById(999999) mengembalikan false/empty');

    // create
    $id = $kategori->create('TestKategoriUnit', 'Deskripsi test unit');
    assertTrue($id !== false && (int)$id > 0, 'create() berhasil dan mengembalikan ID valid');

    if ($id) {
        $found = $kategori->getById((int)$id);
        assertTrue(!empty($found), 'getById() menemukan data yang baru dibuat');
        assertEquals('TestKategoriUnit', $found['nama_kategori'] ?? '', 'nama_kategori tersimpan dengan benar');

        // update
        $updated = $kategori->update((int)$id, 'TestKategoriUpdated', 'Deskripsi updated');
        assertTrue($updated, 'update() berhasil');

        $after = $kategori->getById((int)$id);
        assertEquals('TestKategoriUpdated', $after['nama_kategori'] ?? '', 'nama_kategori terupdate');

        // delete (cleanup)
        $deleted = $kategori->delete((int)$id);
        assertTrue($deleted, 'delete() berhasil');

        $afterDel = $kategori->getById((int)$id);
        assertFalse((bool)$afterDel, 'Data sudah terhapus setelah delete()');
    }
});
?>
