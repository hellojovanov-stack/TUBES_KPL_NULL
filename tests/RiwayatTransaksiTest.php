<?php

/**
 * tests/RiwayatTransaksiTest.php — Unit Test untuk Model RiwayatTransaksi
 */

runSection('RiwayatTransaksiTest — Model CRUD', function () {
    $riwayat = new RiwayatTransaksi();

    $all = $riwayat->getAll();
    assertTrue(is_array($all), 'getAll() mengembalikan array');

    $notFound = $riwayat->getById(999999);
    assertFalse((bool)$notFound, 'getById(999999) mengembalikan false/empty');

    // create
    $id = $riwayat->create(50000, 3, 'test_kasir');
    assertTrue($id !== false && (int)$id > 0, 'create() berhasil dan mengembalikan ID valid');

    if ($id) {
        $found = $riwayat->getById((int)$id);
        assertTrue(!empty($found), 'getById() menemukan riwayat yang baru dibuat');
        assertEquals(50000, (int)($found['total_bayar'] ?? 0), 'total_bayar tersimpan dengan benar');
        assertEquals(3, (int)($found['jumlah_item'] ?? 0), 'jumlah_item tersimpan dengan benar');
        assertEquals('test_kasir', $found['kasir'] ?? '', 'kasir tersimpan dengan benar');

        // Cleanup: hapus riwayat test
        $db = new Database();
        $conn = $db->connect();
        $conn->prepare("DELETE FROM riwayat_transaksi WHERE id = :id")->execute([':id' => $id]);
        assertTrue(true, 'Cleanup riwayat test berhasil');
    }
});

runSection('RiwayatTransaksiTest — Validasi DbC', function () {
    // total_bayar negatif seharusnya tidak diterima
    $threw = false;
    try {
        DbC::requirePositive(-1, 'total_bayar');
    } catch (InvalidArgumentException $e) {
        $threw = true;
    }
    assertTrue($threw, 'DbC::requirePositive() menolak total_bayar negatif');

    // kasir kosong seharusnya tidak diterima
    $threw = false;
    try {
        DbC::requireNonEmpty('', 'kasir');
    } catch (InvalidArgumentException $e) {
        $threw = true;
    }
    assertTrue($threw, 'DbC::requireNonEmpty() menolak kasir kosong');
});
?>
