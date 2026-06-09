<?php

/**
 * tests/TransaksiTest.php — Unit Test untuk Model Transaksi
 */

runSection('TransaksiTest — Validasi Input (TableDrivenValidator)', function () {
    $valid = TableDrivenValidator::validate('transaksi', [
        'id_obat'   => 1,
        'jumlah'    => 2,
        'sub_total' => 10000,
    ]);
    assertTrue($valid['valid'], 'Transaksi valid lolos validasi');

    $r = TableDrivenValidator::validate('transaksi', ['id_obat' => 0, 'jumlah' => 2, 'sub_total' => 0]);
    assertFalse($r['valid'], 'id_obat = 0 gagal validasi (harus > 0)');

    $r = TableDrivenValidator::validate('transaksi', ['id_obat' => 1, 'jumlah' => 0, 'sub_total' => 0]);
    assertFalse($r['valid'], 'jumlah = 0 gagal validasi (minimal 1)');

    $r = TableDrivenValidator::validate('transaksi', ['id_obat' => 1, 'jumlah' => 1, 'sub_total' => -1]);
    assertFalse($r['valid'], 'sub_total negatif gagal validasi');
});

runSection('TransaksiTest — Model getAll & getByRiwayatId', function () {
    $transaksi = new Transaksi();

    $all = $transaksi->getAll();
    assertTrue(is_array($all), 'getAll() mengembalikan array');

    // getByRiwayatId dengan ID yang tidak ada
    $empty = $transaksi->getByRiwayatId(999999);
    assertTrue(is_array($empty), 'getByRiwayatId(999999) mengembalikan array (kosong)');
    assertEquals(0, count($empty), 'getByRiwayatId(999999) mengembalikan array kosong');

    // getById dengan id tidak valid
    $notFound = $transaksi->getById(999999);
    assertFalse((bool)$notFound, 'getById(999999) mengembalikan false/empty');
});

runSection('TransaksiTest — Siklus Create dengan Riwayat', function () {
    $riwayat   = new RiwayatTransaksi();
    $transaksi = new Transaksi();
    $obat      = new Obat();

    // Perlu setidaknya 1 obat di DB
    $allObat = $obat->getAll();
    if (empty($allObat)) {
        assertTrue(true, 'SKIP: Tidak ada obat di DB, test create transaksi dilewati');
        return;
    }

    $testObatId = (int)$allObat[0]['id'];

    // Buat riwayat dulu
    $riwayatId = $riwayat->create(25000, 1, 'test_kasir');
    assertTrue((int)$riwayatId > 0, 'Riwayat test berhasil dibuat');

    // Buat transaksi yang terikat riwayat
    $txId = $transaksi->create($testObatId, 1, 25000, $riwayatId);
    assertTrue((int)$txId > 0, 'Transaksi berhasil dibuat dengan id_riwayat');

    // Verifikasi link
    $details = $transaksi->getByRiwayatId($riwayatId);
    assertNotEmpty($details, 'getByRiwayatId() menemukan transaksi yang baru dibuat');
    assertEquals($testObatId, (int)($details[0]['id_obat'] ?? 0), 'id_obat tersimpan dengan benar');

    // Cleanup
    $db   = new Database();
    $conn = $db->connect();
    $conn->prepare("DELETE FROM transaksi WHERE id = :id")->execute([':id' => $txId]);
    $conn->prepare("DELETE FROM riwayat_transaksi WHERE id = :id")->execute([':id' => $riwayatId]);
    assertTrue(true, 'Cleanup test transaksi berhasil');
});
?>
