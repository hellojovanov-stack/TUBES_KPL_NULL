<?php

/**
 * tests/SupplierTest.php — Unit Test untuk Model Supplier
 */

runSection('SupplierTest — Validasi Input', function () {
    $valid = TableDrivenValidator::validate('supplier', [
        'nama_supplier' => 'Kimia Farma',
        'alamat'        => 'Jl. Contoh No. 1',
        'telepon'       => '021-1234567',
    ]);
    assertTrue($valid['valid'], 'Supplier valid lolos validasi');

    $r = TableDrivenValidator::validate('supplier', ['nama_supplier' => '']);
    assertFalse($r['valid'], 'Supplier nama kosong gagal validasi');

    $r = TableDrivenValidator::validate('supplier', [
        'nama_supplier' => 'Valid',
        'telepon'       => str_repeat('1', 51),
    ]);
    assertFalse($r['valid'], 'Supplier telepon > 50 karakter gagal validasi');
});

runSection('SupplierTest — Model CRUD', function () {
    $supplier = new Supplier();

    $all = $supplier->getAll();
    assertTrue(is_array($all), 'getAll() mengembalikan array');

    $notFound = $supplier->getById(999999);
    assertFalse((bool)$notFound, 'getById(999999) mengembalikan false/empty');

    // create
    $id = $supplier->create('TestSupplierUnit', 'Jl. Test No. 99', '021-9999999');
    assertTrue($id !== false && (int)$id > 0, 'create() berhasil dan mengembalikan ID valid');

    if ($id) {
        $found = $supplier->getById((int)$id);
        assertTrue(!empty($found), 'getById() menemukan data yang baru dibuat');
        assertEquals('TestSupplierUnit', $found['nama_supplier'] ?? '', 'nama_supplier tersimpan dengan benar');

        // update
        $updated = $supplier->update((int)$id, 'TestSupplierUpdated', 'Jl. Update No. 1', '021-0000001');
        assertTrue($updated, 'update() berhasil');

        $after = $supplier->getById((int)$id);
        assertEquals('TestSupplierUpdated', $after['nama_supplier'] ?? '', 'nama_supplier terupdate');

        // delete (cleanup)
        $deleted = $supplier->delete((int)$id);
        assertTrue($deleted, 'delete() berhasil');

        $afterDel = $supplier->getById((int)$id);
        assertFalse((bool)$afterDel, 'Data sudah terhapus setelah delete()');
    }
});
?>
